<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class VendorVettingStatus extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        $cols = $this->db->getFieldNames('users');

        if (! in_array('vendor_status', $cols, true)) {
            $this->db->query("ALTER TABLE `users` ADD COLUMN `vendor_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
        }
        if (! in_array('vendor_status_reason', $cols, true)) {
            $this->db->query('ALTER TABLE `users` ADD COLUMN `vendor_status_reason` TEXT NULL DEFAULT NULL');
        }
        if (! in_array('vendor_status_reviewed_by', $cols, true)) {
            $this->db->query('ALTER TABLE `users` ADD COLUMN `vendor_status_reviewed_by` INT(11) NULL DEFAULT NULL');
        }
        if (! in_array('vendor_status_reviewed_at', $cols, true)) {
            $this->db->query('ALTER TABLE `users` ADD COLUMN `vendor_status_reviewed_at` DATETIME NULL DEFAULT NULL');
        }
        if (! in_array('created_at', $cols, true)) {
            $this->db->query('ALTER TABLE `users` ADD COLUMN `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP');
        }

        // Backfill: every vendor existing at migration time goes live-as-is.
        // Only registrations that happen after this migration start 'pending'.
        $this->db->query("UPDATE `users` SET `vendor_status` = 'approved' WHERE `role` = 'vendor'");
    }

    public function down(): void
    {
        if (! $this->db->tableExists('users')) {
            return;
        }

        foreach (['created_at', 'vendor_status_reviewed_at', 'vendor_status_reviewed_by', 'vendor_status_reason', 'vendor_status'] as $col) {
            $cols = $this->db->getFieldNames('users');
            if (in_array($col, $cols, true)) {
                $this->db->query("ALTER TABLE `users` DROP COLUMN `{$col}`");
            }
        }
    }
}
