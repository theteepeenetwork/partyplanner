<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\TenantBookingFlow;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * TenantBookingFlow::estimatorModel — maps each pricing_type to the storefront
 * instant-quote card's control/estimate shape. Display/estimation only; the
 * exact charge still comes from EventQuoteBuilder on the service page.
 *
 * @internal
 */
final class TenantEstimatorModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace;
    private TenantBookingFlow $flow;
    private int $svc = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->flow = new TenantBookingFlow();
    }

    /** @return int private_event_pricing id */
    private function seedPrivate(string $type, float $price = 0): int
    {
        $this->db->table('services')->insert(['vendor_id' => 1, 'title' => 'S', 'status' => 'active']);
        $this->svc = (int) $this->db->insertID();
        $row       = ['service_id' => $this->svc, 'pricing_type' => $type];
        if ($this->db->fieldExists('price', 'services_private_event_pricing')) {
            $row['price'] = $price;
        }
        $this->db->table('services_private_event_pricing')->insert($row);

        return (int) $this->db->insertID();
    }

    public function testGuestBasedIsPerGuestSliderWithBands(): void
    {
        $pid = $this->seedPrivate('guest_based_pricing');
        $this->db->table('services_guest_based_pricing')->insert(['service_id' => $this->svc, 'private_event_pricing_id' => $pid, 'min_guest' => 1, 'max_guest' => 50, 'guest_price' => 22]);
        $this->db->table('services_guest_based_pricing')->insert(['service_id' => $this->svc, 'private_event_pricing_id' => $pid, 'min_guest' => 51, 'max_guest' => 200, 'guest_price' => 19]);

        $m = $this->flow->estimatorModel($this->svc);
        $this->assertSame('per_guest', $m['model']);
        $this->assertTrue($m['exact']);
        $this->assertSame('guest', $m['unitLabel']);
        $this->assertSame(1, $m['slider']['min']);
        $this->assertSame(200, $m['slider']['max']);
        $this->assertCount(2, $m['slider']['bands']);
    }

    public function testQuantityIsPerUnitSlider(): void
    {
        $pid = $this->seedPrivate('quantity_based_pricing');
        $this->db->table('services_quantity_pricing')->insert(['service_id' => $this->svc, 'private_event_pricing_id' => $pid, 'min_quantity' => 10, 'max_quantity' => 500, 'unit_price' => 3.5, 'unit_label' => 'item']);

        $m = $this->flow->estimatorModel($this->svc);
        $this->assertSame('per_unit', $m['model']);
        $this->assertSame('item', $m['unitLabel']);
    }

    public function testHourDurationIsPerHourOptions(): void
    {
        $pid = $this->seedPrivate('custom_duration_pricing');
        foreach ([[2, 200], [3, 300], [4, 400]] as [$d, $p]) {
            $this->db->table('services_custom_duration_pricing')->insert(['service_id' => $this->svc, 'private_event_pricing_id' => $pid, 'duration_type' => 'hour', 'duration' => $d, 'price' => $p]);
        }

        $m = $this->flow->estimatorModel($this->svc);
        $this->assertSame('per_hour', $m['model']);
        $this->assertCount(3, $m['options']);
        $this->assertSame(200.0, $m['options'][0]['price']);
        $this->assertStringStartsWith('duration_', $m['options'][0]['token']);
    }

    public function testTimeBlocksArePerSessionOptions(): void
    {
        $pid = $this->seedPrivate('custom_duration_pricing');
        $this->db->table('service_time_blocks')->insert(['service_id' => $this->svc, 'start_time' => '18:00:00', 'end_time' => '23:00:00', 'price' => 750]);

        $m = $this->flow->estimatorModel($this->svc);
        $this->assertSame('per_session', $m['model']);
        $this->assertSame('18:00–23:00', $m['options'][0]['label']);
        $this->assertStringStartsWith('timeblock_', $m['options'][0]['token']);
    }

    public function testSinglePackageIsFixed(): void
    {
        $pid = $this->seedPrivate('tiered_packages_pricing');
        $this->db->table('services_tiered_packages_pricing')->insert(['service_id' => $this->svc, 'private_event_pricing_id' => $pid, 'package_name' => 'All in', 'package_price' => 600]);

        $m = $this->flow->estimatorModel($this->svc);
        $this->assertSame('fixed', $m['model']);
        $this->assertSame(600.0, $m['fixed']);
    }

    public function testMultiplePackagesArePackageOptions(): void
    {
        $pid = $this->seedPrivate('tiered_packages_pricing');
        $this->db->table('services_tiered_packages_pricing')->insert(['service_id' => $this->svc, 'private_event_pricing_id' => $pid, 'package_name' => 'Silver', 'package_price' => 400]);
        $this->db->table('services_tiered_packages_pricing')->insert(['service_id' => $this->svc, 'private_event_pricing_id' => $pid, 'package_name' => 'Gold', 'package_price' => 700]);

        $m = $this->flow->estimatorModel($this->svc);
        $this->assertSame('package', $m['model']);
        $this->assertCount(2, $m['options']);
    }

    public function testUnconfiguredIsQuoteOnly(): void
    {
        $this->seedPrivate('corporate_event_pricing'); // no sub-rows, no flat price
        $m = $this->flow->estimatorModel($this->svc);
        $this->assertSame('quote_only', $m['model']);
        $this->assertFalse($m['exact']);
    }

    public function testFlatPriceWithNoTiersIsFixed(): void
    {
        if (! $this->db->fieldExists('price', 'services_private_event_pricing')) {
            $this->markTestSkipped('no flat price column in this schema');
        }
        $this->seedPrivate('guest_based_pricing', 250); // flat price, but no guest bands
        $m = $this->flow->estimatorModel($this->svc);
        $this->assertSame('fixed', $m['model']);
        $this->assertSame(250.0, $m['fixed']);
    }
}
