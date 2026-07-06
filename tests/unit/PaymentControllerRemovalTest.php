<?php

declare(strict_types=1);

namespace Tests\Unit;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Confirms the orphaned PaymentController stub (hardcoded £15 PaymentIntent)
 * has been fully removed: the route no longer resolves to any handler.
 *
 * @internal
 */
final class PaymentControllerRemovalTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'Tests\Support';

    public function testCreatePaymentIntentRouteNoLongerExists(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->post('payment/createPaymentIntent');
    }
}
