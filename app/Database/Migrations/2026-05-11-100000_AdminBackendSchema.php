<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdminBackendSchema extends Migration
{
    public function up(): void
    {
        if ($this->db->DBDriver === 'MySQLi' && $this->db->tableExists('users')) {
            $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('customer','vendor','admin') NOT NULL");
        }

        if (! $this->db->tableExists('cms_pages')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'slug' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'content' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'meta_title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'meta_description' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                    'null'       => true,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'draft',
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
            $this->forge->addUniqueKey('slug');
            $this->forge->createTable('cms_pages', true);
        }

        if ($this->db->tableExists('chat_rooms') && ! $this->db->fieldExists('flagged_for_review', 'chat_rooms')) {
            $this->forge->addColumn('chat_rooms', [
                'flagged_for_review' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                    'after'      => 'service_id',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('cms_pages')) {
            $this->forge->dropTable('cms_pages', true);
        }

        if ($this->db->tableExists('chat_rooms') && $this->db->fieldExists('flagged_for_review', 'chat_rooms')) {
            $this->forge->dropColumn('chat_rooms', 'flagged_for_review');
        }

        if ($this->db->DBDriver === 'MySQLi' && $this->db->tableExists('users')) {
            $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('customer','vendor') NOT NULL");
        }
    }
}
