<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Minimal tables for CustomerEventSummary PHPUnit tests.
 */
class CreateTestCustomerEventSummaryTables extends Migration
{
    protected $DBGroup = 'tests';

    public function up(): void
    {
        if (!$this->db->tableExists('events')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'guest_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'event_setting' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'private',
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'active',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('events');
        }

        if (!$this->db->tableExists('event_basket_items')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'event_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'service_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'vendor_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'estimated_total' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                ],
                'deposit_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
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
            $this->forge->createTable('event_basket_items');
        }

        if (!$this->db->tableExists('bookings')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'event_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'pending',
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
            $this->forge->createTable('bookings');
        }

        if (!$this->db->tableExists('booking_items')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'booking_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'service_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'price' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'pending',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('booking_items');
        }
    }

    public function down(): void
    {
        foreach (['booking_items', 'bookings', 'event_basket_items', 'events'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }
}
