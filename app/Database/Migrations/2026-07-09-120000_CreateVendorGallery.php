<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Dedicated storefront gallery images for the "Recent events" band — a separate
 * upload source from service-card imagery, so the band never echoes the service
 * photos. One row per uploaded photo, owned by the vendor.
 */
class CreateVendorGallery extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('vendor_site_gallery')) {
            return;
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'vendor_id'  => ['type' => 'INT', 'constraint' => 11],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'sort_order' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('vendor_id');
        $this->forge->createTable('vendor_site_gallery');
    }

    public function down(): void
    {
        $this->forge->dropTable('vendor_site_gallery', true);
    }
}
