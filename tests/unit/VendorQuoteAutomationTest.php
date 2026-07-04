<?php

namespace Tests\Unit;

use App\Libraries\VendorQuoteAutomation;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class VendorQuoteAutomationTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The vendor_quote_settings table is created by the raw database_quote_automation.sql
        // import, which never reaches the in-memory SQLite test database. Build it here so the
        // automation library can be exercised in isolation. JSON columns become TEXT under SQLite.
        $forge = \Config\Database::forge();
        $forge->addField([
            'id'                           => ['type' => 'INTEGER', 'auto_increment' => true],
            'vendor_id'                    => ['type' => 'INTEGER'],
            'service_id'                   => ['type' => 'INTEGER', 'null' => true],
            'auto_accept_enabled'          => ['type' => 'INTEGER', 'default' => 0],
            'max_auto_accept_amount'       => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'require_within_travel_radius' => ['type' => 'INTEGER', 'default' => 1],
            'min_lead_days'                => ['type' => 'INTEGER', 'default' => 0],
            'allowed_event_settings'       => ['type' => 'TEXT', 'null' => true],
            'blackout_respect'             => ['type' => 'INTEGER', 'default' => 1],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('vendor_quote_settings', true);
    }

    protected function tearDown(): void
    {
        \Config\Database::forge()->dropTable('vendor_quote_settings', true);

        parent::tearDown();
    }

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
