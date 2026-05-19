<?php

namespace Tests\Unit;

use App\Libraries\VendorQuoteAutomation;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class VendorQuoteAutomationTest extends CIUnitTestCase
{
    public function testRejectsWhenAutoAcceptDisabled(): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('vendor_quote_settings')) {
            $this->markTestSkipped('vendor_quote_settings not present in test database');
        }

        $automation = new VendorQuoteAutomation();
        $result = $automation->evaluateAfterCheckout(
            ['id' => 1, 'price' => 100, 'event_date' => date('Y-m-d', strtotime('+30 days')), 'event_setting' => 'private'],
            ['total' => 100, 'warnings' => [], 'errors' => []],
            99,
            1
        );
        $this->assertFalse($result['auto_accepted']);
        $this->assertSame('auto_accept_disabled', $result['reason']);
    }
}
