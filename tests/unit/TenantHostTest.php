<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\TenantHost;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Host parsing for white-label tenant resolution (T2). The contract:
 * only `<slug>.<baseDomain>` counts as a tenant host; the base domain,
 * www, and every unrelated host are the marketplace (null).
 *
 * @internal
 */
final class TenantHostTest extends CIUnitTestCase
{
    private const BASE = 'partyplanner.test';

    #[DataProvider('provideSubdomainFromHost')]
    public function testSubdomainFromHost(string $host, ?string $expected): void
    {
        $this->assertSame($expected, TenantHost::subdomainFromHost($host, self::BASE));
    }

    /**
     * @return array<string, array{string, string|null}>
     */
    public static function provideSubdomainFromHost(): iterable
    {
        return [
            'tenant subdomain'            => ['vendorone.partyplanner.test', 'vendorone'],
            'tenant subdomain with port'  => ['vendorone.partyplanner.test:8080', 'vendorone'],
            'uppercase host'              => ['VendorOne.PartyPlanner.TEST', 'vendorone'],
            'trailing dot (FQDN form)'    => ['vendorone.partyplanner.test.', 'vendorone'],
            'base domain is marketplace'  => ['partyplanner.test', null],
            'base with port'              => ['partyplanner.test:8080', null],
            'www is marketplace'          => ['www.partyplanner.test', null],
            'www.<sub> strips to null'    => ['www.partyplanner.test:80', null],
            'unrelated host (live site)'  => ['partyplanner.home', null],
            'unrelated subdomain host'    => ['vendorone.partyplanner.home', null],
            'suffix-similar domain'       => ['evilpartyplanner.test', null],
            'empty host'                  => ['', null],
            'multi-label stays unmatched' => ['a.b.partyplanner.test', 'a.b'],
        ];
    }

    public function testProductionBaseDomain(): void
    {
        $this->assertSame('brightsparks', TenantHost::subdomainFromHost('brightsparks.partysmith.co.uk', 'partysmith.co.uk'));
        $this->assertNull(TenantHost::subdomainFromHost('partysmith.co.uk', 'partysmith.co.uk'));
        $this->assertNull(TenantHost::subdomainFromHost('www.partysmith.co.uk', 'partysmith.co.uk'));
    }

    public function testBaseDomainDefaultsToProduction(): void
    {
        $fromEnv = env('tenant.baseDomain');
        if (is_string($fromEnv) && trim($fromEnv) !== '') {
            // A local .env override is present; the default is not reachable.
            $this->assertSame(strtolower(trim($fromEnv)), TenantHost::baseDomain());

            return;
        }

        $this->assertSame(TenantHost::DEFAULT_BASE_DOMAIN, TenantHost::baseDomain());
    }
}
