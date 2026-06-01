<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class VendorHostProfile extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $cols = $this->db->getFieldNames('users');

        if (! in_array('host_bio', $cols, true)) {
            $this->db->query('ALTER TABLE `users` ADD COLUMN `host_bio` TEXT NULL DEFAULT NULL');
        }
        if (! in_array('host_tagline', $cols, true)) {
            $this->db->query("ALTER TABLE `users` ADD COLUMN `host_tagline` VARCHAR(255) NULL DEFAULT NULL");
        }
        if (! in_array('host_quote', $cols, true)) {
            $this->db->query('ALTER TABLE `users` ADD COLUMN `host_quote` TEXT NULL DEFAULT NULL');
        }
        if (! in_array('host_plays', $cols, true)) {
            $this->db->query('ALTER TABLE `users` ADD COLUMN `host_plays` TEXT NULL DEFAULT NULL');
        }
        if (! in_array('host_photo_path', $cols, true)) {
            $this->db->query("ALTER TABLE `users` ADD COLUMN `host_photo_path` VARCHAR(500) NULL DEFAULT NULL");
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('users')) {
            return;
        }
        foreach (['host_bio', 'host_tagline', 'host_quote', 'host_plays', 'host_photo_path'] as $col) {
            $cols = $this->db->getFieldNames('users');
            if (in_array($col, $cols, true)) {
                $this->db->query("ALTER TABLE `users` DROP COLUMN `{$col}`");
            }
        }
    }
}
