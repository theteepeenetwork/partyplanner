<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * One partial (tenant/_price.php) renders all three pricing shapes — flat,
 * per-guest, per-hour — with the same markup and weights: "from" (lead,
 * regular), amount (semibold), unit qualifier (regular, secondary). Prices
 * that resolve to £0 (custom-quote / unpriced) render nothing rather than
 * "from £0".
 *
 * @internal
 */
final class TenantPricePartialTest extends CIUnitTestCase
{
    private function render(array $from): string
    {
        return view('tenant/_price', ['from' => $from]);
    }

    public function testFlatPriceHasNoUnit(): void
    {
        $html = $this->render(['amount' => 400, 'per' => '']);
        $this->assertStringContainsString('sf-price', $html);
        $this->assertStringContainsString('from', $html);
        $this->assertStringContainsString('£400', $html);
        $this->assertStringNotContainsString('class="per"', $html);
    }

    public function testPerGuestPriceRendersUnit(): void
    {
        $html = $this->render(['amount' => 5, 'per' => 'guest']);
        $this->assertStringContainsString('£5', $html);
        $this->assertStringContainsString('/guest', $html);
        $this->assertStringContainsString('class="per"', $html);
    }

    public function testPerHourPriceRendersUnit(): void
    {
        $html = $this->render(['amount' => 250, 'per' => 'hour']);
        $this->assertStringContainsString('£250', $html);
        $this->assertStringContainsString('/hour', $html);
    }

    public function testDecimalAmountKeepsTwoPlaces(): void
    {
        $html = $this->render(['amount' => 12.5, 'per' => 'guest']);
        $this->assertStringContainsString('£12.50', $html);
    }

    public function testZeroAmountRendersNothing(): void
    {
        $this->assertSame('', trim($this->render(['amount' => 0, 'per' => ''])));
    }
}
