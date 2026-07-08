<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Selectable storefront colour theme (Generic storefront redesign): one of the
 * curated presets in App\Libraries\StorefrontThemes, chosen by the vendor on
 * "My site" and applied across the storefront + checkout. Nullable — a null
 * value resolves to the default theme at render time.
 */
class AddThemeToVendorSites extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('vendor_sites') && ! $this->db->fieldExists('theme', 'vendor_sites')) {
            $this->forge->addColumn('vendor_sites', [
                'theme' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'secondary_color',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('vendor_sites') && $this->db->fieldExists('theme', 'vendor_sites')) {
            $this->forge->dropColumn('vendor_sites', 'theme');
        }
    }
}
