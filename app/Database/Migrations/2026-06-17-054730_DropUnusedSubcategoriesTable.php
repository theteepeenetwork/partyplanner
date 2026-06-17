<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Drops the legacy `subcategories` table. All subcategory logic uses the
 * data-driven `categories` adjacency tree (parent_id); this table was created
 * by the original schema dump but is referenced by no live code (its
 * SubcategoryModel was unused and is removed alongside this migration).
 */
class DropUnusedSubcategoriesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('subcategories')) {
            $this->forge->dropTable('subcategories', true);
        }
    }

    public function down()
    {
        // Recreate the (empty) legacy structure so the migration is reversible.
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => false, 'auto_increment' => true],
            'category_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('subcategories', true);
    }
}
