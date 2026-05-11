<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CmsPagesSeeder extends Seeder
{
    public function run(): void
    {
        if (! $this->db->tableExists('cms_pages')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $rows = [
            [
                'slug'             => 'homepage',
                'title'            => 'Homepage intro',
                'content'          => '<p class="mb-0">Optional announcement or intro shown at the top of the homepage when published.</p>',
                'meta_title'       => null,
                'meta_description' => null,
                'status'           => 'draft',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'about',
                'title'            => 'About us',
                'content'          => '<p>We connect customers with trusted event vendors.</p>',
                'meta_title'       => 'About — For Your Events',
                'meta_description' => 'Learn about the For Your Events marketplace.',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'how-it-works',
                'title'            => 'How it works',
                'content'          => '<p>Create an event, browse services, add to your basket, and book vendors in a few steps.</p>',
                'meta_title'       => 'How it works',
                'meta_description' => 'How the event marketplace works for customers and vendors.',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'contact',
                'title'            => 'Contact',
                'content'          => '<p>Email us at <strong>support@example.com</strong> (replace with your real address).</p>',
                'meta_title'       => 'Contact',
                'meta_description' => 'Contact For Your Events.',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'vendor-info',
                'title'            => 'Information for vendors',
                'content'          => '<p>List your services, manage bookings, and grow your event business on our platform.</p>',
                'meta_title'       => 'For vendors',
                'meta_description' => 'Vendor information for the marketplace.',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'faq',
                'title'            => 'Frequently asked questions',
                'content'          => '<h5>How do I book a service?</h5><p>Add services to your event basket and complete checkout.</p>',
                'meta_title'       => 'FAQ',
                'meta_description' => 'Common questions about For Your Events.',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        foreach ($rows as $row) {
            $exists = $this->db->table('cms_pages')->where('slug', $row['slug'])->countAllResults();
            if ($exists === 0) {
                $this->db->table('cms_pages')->insert($row);
            }
        }
    }
}
