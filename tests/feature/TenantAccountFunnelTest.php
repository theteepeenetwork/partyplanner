<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Confirmation-page account funnel (Booking Confirmation Redesign, 1a). The
 * guest claims the account created at checkout by setting a password. Security
 * spine: only an account THIS session created may be claimed — a session that
 * merely owns a booking linked to a pre-existing account cannot reset that
 * account's password (takeover guard).
 *
 * @internal
 */
final class TenantAccountFunnelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    private const BASE_DOMAIN = 'partyplanner.test';
    private const OLD_PW      = 'OldGuestPass123';

    protected $namespace;
    private int $customerId;
    private int $bookingId;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('tenant.baseDomain=' . self::BASE_DOMAIN);
        $_ENV['tenant.baseDomain']    = self::BASE_DOMAIN;
        $_SERVER['tenant.baseDomain'] = self::BASE_DOMAIN;

        $vendorId = $this->seedVendor();
        $this->db->table('vendor_sites')->insert([
            'vendor_id' => $vendorId, 'subdomain' => 'acct',
            'business_name' => 'Acct Events', 'status' => 'active',
        ]);
        $this->db->table('services')->insert(['vendor_id' => $vendorId, 'title' => 'Photo Booth', 'status' => 'active']);
        $serviceId = (int) $this->db->insertID();

        $this->db->table('users')->insert([
            'name' => 'Guest Priya', 'username' => 'guest_priya',
            'email' => 'priya@example.test', 'password' => password_hash(self::OLD_PW, PASSWORD_DEFAULT),
            'role' => 'customer',
        ]);
        $this->customerId = (int) $this->db->insertID();

        $this->db->table('events')->insert(['user_id' => $this->customerId, 'title' => 'E', 'date' => date('Y-m-d', strtotime('+30 days'))]);
        $eventId = (int) $this->db->insertID();
        $this->db->table('bookings')->insert(['user_id' => $this->customerId, 'event_id' => $eventId, 'status' => 'pending', 'balance_due' => 733.50]);
        $this->bookingId = (int) $this->db->insertID();
        $this->db->table('booking_items')->insert(['booking_id' => $this->bookingId, 'service_id' => $serviceId, 'status' => 'pending']);
        $this->db->table('payments')->insert(['booking_id' => $this->bookingId, 'amount_paid' => 81.50, 'payment_status' => 'succeeded']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('tenant.baseDomain');
        unset($_ENV['tenant.baseDomain'], $_SERVER['tenant.baseDomain'], $_SERVER['HTTP_HOST']);
    }

    private function seedVendor(): int
    {
        $row = ['name' => 'V', 'username' => 'v_' . uniqid(), 'email' => uniqid('v_') . '@e.test', 'password' => 'h', 'role' => 'vendor'];
        if ($this->db->fieldExists('vendor_status', 'users')) {
            $row['vendor_status'] = 'approved';
        }
        $this->db->table('users')->insert($row);

        return (int) $this->db->insertID();
    }

    private function onTenant(): void
    {
        $_SERVER['HTTP_HOST'] = 'acct.' . self::BASE_DOMAIN;
        service('routes')->resetRoutes();
    }

    /** @return array<string,mixed> */
    private function claimSession(bool $claimable = true): array
    {
        return [
            'tenant_bookings'       => [$this->bookingId],
            'tenant_guest_email'    => 'priya@example.test',
            'tenant_guest_name'     => 'Priya',
            'tenant_claimable_user' => $claimable ? $this->customerId : 0,
        ];
    }

    private function currentPasswordHash(): string
    {
        return (string) $this->db->table('users')->where('id', $this->customerId)->get()->getRowArray()['password'];
    }

    public function testConfirmationShowsCreateAccountFormWhenClaimable(): void
    {
        $this->onTenant();
        $result = $this->withSession($this->claimSession())->get('/booked/' . $this->bookingId);

        $result->assertStatus(200);
        $result->assertSee('Create an account to manage your booking');
        $result->assertSee('/account/create');
        $result->assertSee('booking_id');
    }

    public function testConfirmationHidesFormWhenNotClaimable(): void
    {
        $this->onTenant();
        $result = $this->withSession($this->claimSession(false))->get('/booked/' . $this->bookingId);

        $result->assertStatus(200);
        $result->assertDontSee('action="/account/create"');
        $result->assertSee('You already have an account');
    }

    public function testCreateAccountSetsThePassword(): void
    {
        $this->onTenant();
        $result = $this->withSession($this->claimSession())->post('/account/create', [
            'booking_id'       => $this->bookingId,
            'name'             => 'Priya Patel',
            'username'         => 'priyapatel',
            'password'         => 'brandnewsecret',
            'confirm_password' => 'brandnewsecret',
            'agree_terms'      => '1',
            csrf_token()       => csrf_hash(),
        ]);

        $result->assertRedirect();
        $this->assertTrue(password_verify('brandnewsecret', $this->currentPasswordHash()));
        $this->assertFalse(password_verify(self::OLD_PW, $this->currentPasswordHash()));

        $user = $this->db->table('users')->where('id', $this->customerId)->get()->getRowArray();
        $this->assertSame('Priya Patel', $user['name']);
        $this->assertSame('priyapatel', $user['username']);
    }

    public function testTakeoverBlockedWhenAccountNotClaimable(): void
    {
        // Session owns the booking but the account is NOT the one it created.
        $this->onTenant();
        $result = $this->withSession($this->claimSession(false))->post('/account/create', [
            'booking_id'       => $this->bookingId,
            'password'         => 'attackerpass',
            'confirm_password' => 'attackerpass',
            'agree_terms'      => '1',
            csrf_token()       => csrf_hash(),
        ]);

        $result->assertRedirect();
        // Password unchanged — the pre-existing account was not hijacked.
        $this->assertTrue(password_verify(self::OLD_PW, $this->currentPasswordHash()));
    }

    public function testShortPasswordRejected(): void
    {
        $this->onTenant();
        $this->withSession($this->claimSession())->post('/account/create', [
            'booking_id'       => $this->bookingId,
            'password'         => 'short',
            'confirm_password' => 'short',
            'agree_terms'      => '1',
            csrf_token()       => csrf_hash(),
        ]);

        $this->assertTrue(password_verify(self::OLD_PW, $this->currentPasswordHash()));
    }

    public function testMismatchedPasswordRejected(): void
    {
        $this->onTenant();
        $this->withSession($this->claimSession())->post('/account/create', [
            'booking_id'       => $this->bookingId,
            'password'         => 'brandnewsecret',
            'confirm_password' => 'differentsecret',
            'agree_terms'      => '1',
            csrf_token()       => csrf_hash(),
        ]);

        $this->assertTrue(password_verify(self::OLD_PW, $this->currentPasswordHash()));
    }

    public function testMissingTermsRejected(): void
    {
        $this->onTenant();
        $this->withSession($this->claimSession())->post('/account/create', [
            'booking_id'       => $this->bookingId,
            'password'         => 'brandnewsecret',
            'confirm_password' => 'brandnewsecret',
            csrf_token()       => csrf_hash(),
        ]);

        $this->assertTrue(password_verify(self::OLD_PW, $this->currentPasswordHash()));
    }
}
