<?php

namespace App\Libraries;

use Config\Database;
use Config\SiteNav as SiteNavConfig;

/**
 * Reports missing or non-published CMS rows for URLs linked from global navigation.
 */
class PublicCmsNavHealth
{
    /**
     * @return list<array{type: string, slug: string, label: string, public_url: string, edit_url: ?string, status?: string}>
     */
    public static function navIssues(): array
    {
        $db  = Database::connect();
        $nav = config(SiteNavConfig::class)->cmsBackedNav;

        if (! $db->tableExists('cms_pages')) {
            return [[
                'type'       => 'table_missing',
                'slug'       => '',
                'label'      => 'CMS table',
                'public_url' => '',
                'edit_url'   => null,
                'status'     => '',
            ]];
        }

        $issues = [];
        foreach ($nav as $item) {
            $row = $db->table('cms_pages')->where('slug', $item['slug'])->get()->getRowArray();
            if (! $row) {
                $issues[] = [
                    'type'       => 'missing',
                    'slug'       => $item['slug'],
                    'label'      => $item['label'],
                    'public_url' => site_url($item['route']),
                    'edit_url'   => null,
                ];
                continue;
            }
            if (($row['status'] ?? '') !== 'published') {
                $issues[] = [
                    'type'       => 'draft',
                    'slug'       => $item['slug'],
                    'label'      => $item['label'],
                    'public_url' => site_url($item['route']),
                    'edit_url'   => site_url('/admin/pages/edit/' . $item['slug']),
                    'status'     => (string) ($row['status'] ?? ''),
                ];
            }
        }

        return $issues;
    }

    /**
     * @return array<string, string> slug => label for chrome-linked CMS pages
     */
    public static function navSlugLabels(): array
    {
        $out = [];
        foreach (config(SiteNavConfig::class)->cmsBackedNav as $item) {
            $out[$item['slug']] = $item['label'];
        }

        return $out;
    }
}
