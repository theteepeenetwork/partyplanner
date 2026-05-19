<?php

namespace Tests\Unit;

use App\Libraries\EventQuoteBuilder;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EventQuoteBuilderTest extends CIUnitTestCase
{
    public function testMergeServiceLocationIncludesStrictTravel(): void
    {
        $builder = new EventQuoteBuilder();
        $merged = $builder->mergeServiceLocation(['price' => 10], [
            'strict_travel_radius' => 1,
            'fulfillment_type' => 'postal',
            'postal_fee' => 5.0,
        ]);
        $this->assertSame(1, (int) $merged['strict_travel_radius']);
        $this->assertSame('postal', $merged['fulfillment_type']);
    }
}
