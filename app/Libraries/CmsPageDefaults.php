<?php

namespace App\Libraries;

use Config\Database;

/**
 * Default marketing/CMS pages for header/footer routes and admin editing.
 */
class CmsPageDefaults
{
    /**
     * @return array<string, array<string, string|null>>
     */
    public static function definitions(): array
    {
        return [
            'homepage' => [
                'title'            => 'Welcome',
                'content'          => '<p class="lead">Plan your celebration with trusted local vendors.</p><p><em>This block is editable in <strong>Admin → Pages → homepage</strong>.</em></p>',
                'meta_title'       => null,
                'meta_description' => null,
                'status'           => 'published',
            ],
            'about' => [
                'title'            => 'About us',
                'content'          => '<p>We connect people planning celebrations with trusted event suppliers.</p>',
                'meta_title'       => 'About — For Your Events',
                'meta_description' => 'Learn about the For Your Events marketplace.',
                'status'           => 'published',
            ],
            'how-it-works' => [
                'title'            => 'How it works',
                'content'          => '<p class="lead">Plan your event and book suppliers in a few clear steps.</p>'
                    . '<h2 class="h4 mt-4">For customers</h2>'
                    . '<ol><li><strong>Create your event</strong> — date, location, guest count, and the type of occasion.</li>'
                    . '<li><strong>Browse services</strong> — search and filter by category, compare listings, and save favourites.</li>'
                    . '<li><strong>Add to your plan</strong> — add services to your event basket and send booking requests to vendors.</li>'
                    . '<li><strong>Stay in control</strong> — track pending, accepted, and declined requests in My Bookings, message vendors from Messages, and review payments in Payments.</li></ol>'
                    . '<h2 class="h4 mt-4">For vendors</h2>'
                    . '<ol><li><strong>Register as a vendor</strong> and build your service listings with clear pricing and policies.</li>'
                    . '<li><strong>Respond to bookings</strong> from your dashboard; accept or decline with one click.</li>'
                    . '<li><strong>Use your calendar</strong> to see upcoming work tied to customer events.</li></ol>'
                    . '<p class="mt-3 mb-0 text-muted">Administrators can refine this text under <strong>Admin → Pages</strong>.</p>',
                'meta_title'       => 'How it works',
                'meta_description' => 'How the For Your Events marketplace works for customers and vendors.',
                'status'           => 'published',
            ],
            'contact' => [
                'title'            => 'Contact',
                'content'          => '<p>Email us at <strong>support@example.com</strong> (replace with your live support address).</p>',
                'meta_title'       => 'Contact',
                'meta_description' => 'Contact For Your Events.',
                'status'           => 'published',
            ],
            'vendor-info' => [
                'title'            => 'Information for vendors',
                'content'          => '<p>List your services, respond to booking requests, and grow your event business from a single dashboard.</p>',
                'meta_title'       => 'For vendors',
                'meta_description' => 'Vendor information for the marketplace.',
                'status'           => 'published',
            ],
            'faq' => [
                'title'            => 'Frequently asked questions',
                'content'          => '<h5>How do I book a service?</h5><p>Add services to your event basket, send booking requests, and complete payment when suppliers confirm.</p>'
                    . '<h5 class="mt-4">How do I message a vendor?</h5><p>Messaging opens once you have an eligible booking for that service.</p>',
                'meta_title'       => 'FAQ',
                'meta_description' => 'Common questions about For Your Events.',
                'status'           => 'published',
            ],
        ];
    }

    /**
     * Insert any default pages that are not already in cms_pages.
     */
    public static function seedMissing(): int
    {
        $db = Database::connect();
        if (! $db->tableExists('cms_pages')) {
            return 0;
        }

        $now      = date('Y-m-d H:i:s');
        $inserted = 0;

        foreach (self::definitions() as $slug => $row) {
            if ($db->table('cms_pages')->where('slug', $slug)->countAllResults() > 0) {
                continue;
            }

            $db->table('cms_pages')->insert(array_merge($row, [
                'slug'       => $slug,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
            $inserted++;
        }

        return $inserted;
    }

    public static function ensureSlug(string $slug): bool
    {
        if (! isset(self::definitions()[$slug])) {
            return false;
        }

        $db = Database::connect();
        if (! $db->tableExists('cms_pages')) {
            return false;
        }

        if ($db->table('cms_pages')->where('slug', $slug)->countAllResults() > 0) {
            return true;
        }

        $now = date('Y-m-d H:i:s');
        $row = self::definitions()[$slug];
        $db->table('cms_pages')->insert(array_merge($row, [
            'slug'       => $slug,
            'created_at' => $now,
            'updated_at' => $now,
        ]));

        return true;
    }
}
