<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\DepositCalculator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DepositCalculatorTest extends CIUnitTestCase
{
    public function testForTotalReturnsTenPercentRoundedToTwoDecimals(): void
    {
        $this->assertEqualsWithDelta(50.0, DepositCalculator::forTotal(500.0), 0.001);
    }

    public function testForTotalRoundsPennyBoundaryCorrectly(): void
    {
        // 333.33 * 0.10 = 33.333 -> rounds to 33.33 (naive truncation would give 33.33 too,
        // so use a case where rounding half-up differs from truncation: 333.35 * 0.10 = 33.335 -> 33.34
        $this->assertEqualsWithDelta(33.33, DepositCalculator::forTotal(333.33), 0.001);
        $this->assertEqualsWithDelta(33.34, DepositCalculator::forTotal(333.35), 0.001);
    }

    public function testForTotalReturnsZeroForZeroTotal(): void
    {
        $this->assertSame(0.0, DepositCalculator::forTotal(0.0));
    }

    public function testPercentDisplayReturnsTen(): void
    {
        $this->assertSame(10, DepositCalculator::percentDisplay());
    }
}
