<?php

namespace App\Database\Seeds;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Local-dev seed for the white-label pilot: one vendor_sites row attached to
 * the QA vendor (vendor1@v.com — run `php spark db:seed QASeeder` first if it
 * doesn't exist), reachable at http://vendorone.<tenant.baseDomain>/.
 *
 * Run with:   php spark db:seed VendorSiteSeeder
 * Safe to run repeatedly — updates the existing row in place.
 */
class VendorSiteSeeder extends Seeder
{
    private const SUBDOMAIN = 'vendorone';

    public function run(): void
    {
        $vendor = $this->db->table('users')
            ->where('email', 'vendor1@v.com')
            ->where('role', 'vendor')
            ->get()->getRowArray();

        if ($vendor === null) {
            $vendor = $this->db->table('users')
                ->where('role', 'vendor')
                ->orderBy('id')
                ->get(1)->getRowArray();
        }

        if ($vendor === null) {
            CLI::error('VendorSiteSeeder: no vendor account found — run `php spark db:seed QASeeder` first.');

            return;
        }

        $now  = date('Y-m-d H:i:s');
        $site = [
            'vendor_id'       => (int) $vendor['id'],
            'subdomain'       => self::SUBDOMAIN,
            'business_name'   => 'Vendor One Events',
            'logo_path'       => null,
            'primary_color'   => '#3B2F63', // deep plum — visibly distinct from the marketplace green
            'secondary_color' => '#C0763E', // burnt amber
            'about_text'      => 'Family-run events team covering London and the South East.',
            'phone'           => '020 7946 0958',
            'status'          => 'active',
            'updated_at'      => $now,
        ];

        $existing = $this->db->table('vendor_sites')
            ->groupStart()
            ->where('vendor_id', (int) $vendor['id'])
            ->orWhere('subdomain', self::SUBDOMAIN)
            ->groupEnd()
            ->get()->getRowArray();

        if ($existing !== null) {
            $this->db->table('vendor_sites')->where('id', (int) $existing['id'])->update($site);
            CLI::write('VendorSiteSeeder: updated site #' . $existing['id'] . ' (' . self::SUBDOMAIN . ') for vendor #' . $vendor['id'], 'green');

            return;
        }

        $site['created_at'] = $now;
        $this->db->table('vendor_sites')->insert($site);
        CLI::write('VendorSiteSeeder: created site ' . self::SUBDOMAIN . ' for vendor #' . $vendor['id'], 'green');
    }
}
