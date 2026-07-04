<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Additional tables/columns for BookingConfirmation + VendorQuoteAutomation PHPUnit tests.
 * Extends the bookings/booking_items tables created by CreateTestCustomerEventSummaryTables.
 */
class CreateTestBookingConfirmationTables extends Migration
{
    protected $DBGroup = 'tests';

    public function up(): void
    {
        if ($this->db->tableExists('bookings')) {
            $fields = [
                'payment_intent_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'balance_due' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => true,
                ],
                'payment_plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                ],
            ];
            foreach ($fields as $name => $def) {
                if (!$this->db->fieldExists($name, 'bookings')) {
                    $this->forge->addColumn('bookings', [$name => $def]);
                }
            }
        }

        if ($this->db->tableExists('booking_items')) {
            $fields = [
                'quote_breakdown' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'quote_warnings' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ];
            foreach ($fields as $name => $def) {
                if (!$this->db->fieldExists($name, 'booking_items')) {
                    $this->forge->addColumn('booking_items', [$name => $def]);
                }
            }
        }

        if (!$this->db->tableExists('payments')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'booking_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'payment_intent_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'payment_status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'pending',
                ],
                'amount_paid' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => true,
                ],
                'currency' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => true,
                ],
                'payment_method' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'payment_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('payments');
        }

        if (!$this->db->tableExists('services')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'vendor_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'title' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services');
        }

        if (!$this->db->tableExists('vendor_quote_settings')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'vendor_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'service_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'auto_accept_enabled' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ],
                'max_auto_accept_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => true,
                ],
                'require_within_travel_radius' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ],
                'min_lead_days' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'allowed_event_settings' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'blackout_respect' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('vendor_quote_settings');
        }

        if (!$this->db->tableExists('quote_automation_log')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'booking_item_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'action' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'details' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('quote_automation_log');
        }
    }

    public function down(): void
    {
        foreach (['quote_automation_log', 'vendor_quote_settings', 'services', 'payments'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }

        if ($this->db->tableExists('booking_items')) {
            foreach (['quote_warnings', 'quote_breakdown'] as $col) {
                if ($this->db->fieldExists($col, 'booking_items')) {
                    $this->forge->dropColumn('booking_items', $col);
                }
            }
        }

        if ($this->db->tableExists('bookings')) {
            foreach (['payment_plan', 'balance_due', 'payment_intent_id'] as $col) {
                if ($this->db->fieldExists($col, 'bookings')) {
                    $this->forge->dropColumn('bookings', $col);
                }
            }
        }
    }
}
