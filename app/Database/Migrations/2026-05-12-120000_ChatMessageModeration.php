<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChatMessageModeration extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('chat_messages')) {
            return;
        }

        $fields = [
            'original_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'moderation_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'clean',
            ],
            'admin_note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'profanity_matches' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'reviewed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        foreach ($fields as $name => $def) {
            if (! $this->db->fieldExists($name, 'chat_messages')) {
                $this->forge->addColumn('chat_messages', [$name => $def]);
            }
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('chat_messages')) {
            return;
        }

        foreach (['reviewed_at', 'reviewed_by', 'profanity_matches', 'admin_note', 'moderation_status', 'original_message'] as $col) {
            if ($this->db->fieldExists($col, 'chat_messages')) {
                $this->forge->dropColumn('chat_messages', $col);
            }
        }
    }
}
