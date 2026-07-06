<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * White-label foundation (T1): one row per vendor storefront on a
 * `<subdomain>.<tenant.baseDomain>` host. A vendor has at most one site
 * (unique vendor_id) and a subdomain maps to at most one vendor.
 */
class CreateVendorSites extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('vendor_sites')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'vendor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'subdomain' => [
                'type'       => 'VARCHAR',
                'constraint' => 63, // single DNS label
            ],
            'business_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'logo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'primary_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 9, // #RRGGBB / #RRGGBBAA
                'null'       => true,
            ],
            'secondary_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 9,
                'null'       => true,
            ],
            'about_text' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'suspended'],
                'default'    => 'active',
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
        $this->forge->addUniqueKey('vendor_id');
        $this->forge->addUniqueKey('subdomain');

        // The base schema (`users` et al.) comes from the SQL dump imports, not
        // migrations, so guard the FK the same way sibling migrations guard
        // their ALTERs: skip it when the parent table isn't there yet.
        if ($this->db->tableExists('users')) {
            $this->forge->addForeignKey('vendor_id', 'users', 'id', 'CASCADE', 'CASCADE');
        }

        $this->forge->createTable('vendor_sites');
    }

    public function down(): void
    {
        $this->forge->dropTable('vendor_sites', true);
    }
}
