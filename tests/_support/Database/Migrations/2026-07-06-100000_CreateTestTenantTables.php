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
        $this->forge->dropTable('service_images', true);
        $this->forge->dropTable('services', true);
        $this->forge->dropTable('users', true);
    }
}
