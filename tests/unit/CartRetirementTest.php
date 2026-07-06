<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Confirms the legacy cart flow is fully retired: GET entry points redirect
 * to the event-basket flow with a flash message, and the former POST
 * money-path routes (submit / submitToVendors / processPayment / update)
 * no longer resolve to any handler.
 *
 * @internal
 */
final class CartRetirementTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'Tests\Support';

    public function testCartIndexRedirectsToProfileEventsWithFlash(): void
    {
        $result = $this->get('cart');

        $result->assertRedirectTo('/profile/events');

        $session = session();
        $this->assertSame(
            'The cart has been retired. You can now add services directly to your events.',
            $session->getFlashdata('info'),
        );
    }

    public function testCartAddRedirectsToProfileEventsWithFlash(): void
    {
        $result = $this->get('cart/add/1');

        $result->assertRedirectTo('/profile/events');

        $session = session();
        $this->assertSame(
            'The cart has been retired. Please select an event and add services directly.',
            $session->getFlashdata('info'),
        );
    }

    public function testCartAddViaPostAlsoRedirects(): void
    {
        $result = $this->post('cart/add/1', [csrf_token() => csrf_hash()]);

        $result->assertRedirectTo('/profile/events');
    }

    public function testCartRemoveRouteNoLongerExists(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->get('cart/remove/1');
    }

    public function testCartUpdateRouteNoLongerExists(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->post('cart/update/1');
    }

    public function testCartSubmitRouteNoLongerExists(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->post('cart/submit');
    }

    public function testCartSubmitToVendorsRouteNoLongerExists(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->post('cart/submitToVendors');
    }

    public function testCartProcessPaymentRouteNoLongerExists(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->post('cart/processPayment');
    }
}
