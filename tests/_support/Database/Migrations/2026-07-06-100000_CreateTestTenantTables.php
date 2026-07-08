<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * SQLite-friendly minimal `users`, `services` and `service_images` tables for
 * the white-label tenant feature tests. Timestamped BEFORE the App
 * CreateVendorSites migration so that runs with the users table present and
 * exercises its FK branch. Every create is guarded so this composes with any
 * other support migration that provides the same tables.
 */
class CreateTestTenantTables extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('users')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'auto_increment' => true,
                ],
                'name'     => ['type' => 'VARCHAR', 'constraint' => 100],
                'username' => ['type' => 'VARCHAR', 'constraint' => 255],
                'email'    => ['type' => 'VARCHAR', 'constraint' => 255],
                'password' => ['type' => 'VARCHAR', 'constraint' => 255],
                'role'     => ['type' => 'VARCHAR', 'constraint' => 20],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('users');
        }

        if (! $this->db->tableExists('services')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'auto_increment' => true,
                ],
                'vendor_id'         => ['type' => 'INT', 'constraint' => 11],
                'title'             => ['type' => 'VARCHAR', 'constraint' => 255],
                'short_description' => ['type' => 'TEXT', 'null' => true],
                'description'       => ['type' => 'TEXT', 'null' => true],
                'service_location'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'setup_minutes'     => ['type' => 'INT', 'null' => true],
                'breakdown_minutes' => ['type' => 'INT', 'null' => true],
                'price'             => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
                'status'            => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
                'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services');
        } else {
            // Another support migration (e.g. CreateTestUsersTable) may have
            // created a slimmer `services` table first — add what the tenant
            // storefront tests rely on. Earlier addColumn() calls leave the
            // connection's field cache stale, so clear it before checking.
            $this->db->resetDataCache();

            foreach ([
                'short_description' => ['type' => 'TEXT', 'null' => true],
                'description'       => ['type' => 'TEXT', 'null' => true],
                'service_location'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'setup_minutes'     => ['type' => 'INT', 'null' => true],
                'breakdown_minutes' => ['type' => 'INT', 'null' => true],
                'price'             => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
                'status'            => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            ] as $col => $def) {
                if (! $this->db->fieldExists($col, 'services')) {
                    $this->forge->addColumn('services', [$col => $def]);
                }
            }
        }

        // Columns the tenant guest-checkout writes beyond what earlier support
        // migrations create (events from CreateTestCustomerEventSummaryTables,
        // booking_items from CreateTestBookingConfirmationTables).
        if ($this->db->tableExists('events')) {
            $this->db->resetDataCache();

            foreach ([
                'event_type' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'latitude'   => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
                'longitude'  => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
                'location'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'postcode'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'town_city'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            ] as $col => $def) {
                if (! $this->db->fieldExists($col, 'events')) {
                    $this->forge->addColumn('events', [$col => $def]);
                }
            }
        }

        if ($this->db->tableExists('booking_items')) {
            $this->db->resetDataCache();

            foreach ([
                'quantity'        => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'package_name'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'guest_count'     => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'extras_snapshot' => ['type' => 'TEXT', 'null' => true],
            ] as $col => $def) {
                if (! $this->db->fieldExists($col, 'booking_items')) {
                    $this->forge->addColumn('booking_items', [$col => $def]);
                }
            }
        }

        if (! $this->db->tableExists('chat_rooms')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'auto_increment' => true,
                ],
                'vendor_id'          => ['type' => 'INT', 'constraint' => 11],
                'customer_id'        => ['type' => 'INT', 'constraint' => 11],
                'service_id'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'flagged_for_review' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'created_at'         => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('chat_rooms');
        }

        if (! $this->db->tableExists('chat_messages')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'auto_increment' => true,
                ],
                'chat_room_id'      => ['type' => 'INT', 'constraint' => 11],
                'sender_id'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'receiver_id'       => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'message'           => ['type' => 'TEXT', 'null' => true],
                'original_message'  => ['type' => 'TEXT', 'null' => true],
                'is_read'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'moderation_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'admin_note'        => ['type' => 'TEXT', 'null' => true],
                'profanity_matches' => ['type' => 'TEXT', 'null' => true],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('chat_messages');
        }

        if (! $this->db->tableExists('service_images')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'auto_increment' => true,
                ],
                'service_id' => ['type' => 'INT', 'constraint' => 11],
                'image_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'is_primary' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('service_images');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('chat_messages', true);
        $this->forge->dropTable('chat_rooms', true);
        $this->forge->dropTable('service_images', true);
        $this->forge->dropTable('services', true);
        $this->forge->dropTable('users', true);
    }
}
