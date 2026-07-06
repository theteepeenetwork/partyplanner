<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Minimal `users` table for VendorVetting / VendorAuth PHPUnit tests.
 * VARCHAR stands in for the production ENUM (SQLite has no ENUM type);
 * production enforcement comes from the real VendorVettingStatus migration.
 *
 * Also creates a minimal `services` table if another support migration
 * hasn't already (guarded with tableExists so this never collides with
 * CreateTestBookingConfirmationTables).
 */
class CreateTestUsersTable extends Migration
{
    protected $DBGroup = 'tests';

    public function up(): void
    {
        if (! $this->db->tableExists('users')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'username' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'password' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'role' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                ],
                'vendor_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'default'    => 'pending',
                ],
                'vendor_status_reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'vendor_status_reviewed_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'vendor_status_reviewed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('users');
        }

        if (! $this->db->tableExists('services')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'vendor_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                ],
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('services');
        } else {
            // Another support migration (e.g. CreateTestBookingConfirmationTables) may have
            // already created a minimal `services` table without these columns — the vendor
            // dashboard tabs (Profile::services/vendorMain/hostProfile) query both.
            foreach (['status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'deleted_at'   => ['type' => 'DATETIME', 'null' => true]] as $col => $def) {
                if (! $this->db->fieldExists($col, 'services')) {
                    $this->forge->addColumn('services', [$col => $def]);
                }
            }
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('users')) {
            $this->forge->dropTable('users', true);
        }
    }
}
