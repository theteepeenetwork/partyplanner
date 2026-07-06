<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pricing tables consumed by EventQuoteBuilder::build() that no earlier
 * test-support migration creates. Mirrors the production schema in
 * event_marketplace.sql / database_update.sql / database_fulfillment_extras.sql
 * / database_quantity_pricing.sql, trimmed to the columns the builder reads.
 */
class CreateTestQuoteBuilderPricingTables extends Migration
{
    protected $DBGroup = 'tests';

    public function up(): void
    {
        if (!$this->db->tableExists('services_public_event_pricing')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'commission_percentage' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'min_attendance' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'max_attendance' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'max_pitch_fee' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services_public_event_pricing');
        }

        if (!$this->db->tableExists('services_private_event_pricing')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'pricing_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
                'description' => ['type' => 'TEXT', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services_private_event_pricing');
        }

        if (!$this->db->tableExists('services_guest_based_pricing')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'private_event_pricing_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'min_guest' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'max_guest' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'guest_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services_guest_based_pricing');
        }

        if (!$this->db->tableExists('services_custom_duration_pricing')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'private_event_pricing_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'duration_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'duration' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services_custom_duration_pricing');
        }

        if (!$this->db->tableExists('services_tiered_packages_pricing')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'private_event_pricing_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'package_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'package_description' => ['type' => 'TEXT', 'null' => true],
                'package_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services_tiered_packages_pricing');
        }

        if (!$this->db->tableExists('services_quantity_pricing')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'private_event_pricing_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'unit_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
                'min_quantity' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
                'max_quantity' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'unit_label' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'items'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services_quantity_pricing');
        }

        if (!$this->db->tableExists('services_optional_extras')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'description' => ['type' => 'TEXT', 'null' => true],
                'price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
                'pricing_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'flat'],
                'min_quantity' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'unsigned' => true],
                'max_quantity' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'unsigned' => true],
                'unit_label' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services_optional_extras');
        }

        if (!$this->db->tableExists('services_locations')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'fulfillment_type' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'in_person'],
                'service_location' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'location' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
                'longitude' => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
                'all_travel_included' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'no_travel_limit' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'free_coverage_radius' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'paid_coverage_radius' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'travel_fee_per_km' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
                'postal_fee' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true],
                'free_postage_above' => ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true],
                'delivery_lead_time_days' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'unsigned' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services_locations');
        }

        if (!$this->db->tableExists('service_time_blocks')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'start_time' => ['type' => 'TIME', 'null' => true],
                'end_time' => ['type' => 'TIME', 'null' => true],
                'price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('service_time_blocks');
        }

        if (!$this->db->tableExists('services_corporate_event_pricing')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'pricing_details' => ['type' => 'TEXT', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services_corporate_event_pricing');
        }
    }

    public function down(): void
    {
        foreach ([
            'services_corporate_event_pricing',
            'service_time_blocks',
            'services_locations',
            'services_optional_extras',
            'services_quantity_pricing',
            'services_tiered_packages_pricing',
            'services_custom_duration_pricing',
            'services_guest_based_pricing',
            'services_private_event_pricing',
            'services_public_event_pricing',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }
}
