<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Public navigation targets backed by {@see \App\Controllers\PublicPage::show()}
 * and the `cms_pages` table. Each slug must exist with status <code>published</code>
 * or the public URL returns 404.
 */
class SiteNav extends BaseConfig
{
    /**
     * @var list<array{slug: string, label: string, route: string}>
     */
    public array $cmsBackedNav = [
        ['slug' => 'how-it-works', 'label' => 'How It Works', 'route' => 'how-it-works'],
        ['slug' => 'about', 'label' => 'About', 'route' => 'about'],
        ['slug' => 'contact', 'label' => 'Contact', 'route' => 'contact'],
        ['slug' => 'vendor-info', 'label' => 'Vendor information', 'route' => 'vendor-info'],
        ['slug' => 'faq', 'label' => 'FAQ', 'route' => 'faq'],
    ];
}
