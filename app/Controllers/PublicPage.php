<?php

namespace App\Controllers;

use App\Libraries\CmsPageDefaults;
use App\Models\CmsPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class PublicPage extends BaseController
{
    public function about()
    {
        return $this->show('about');
    }

    public function howItWorks()
    {
        return $this->show('how-it-works');
    }

    public function contact()
    {
        return $this->show('contact');
    }

    public function vendorInfo()
    {
        return $this->show('vendor-info');
    }

    public function faq()
    {
        return $this->show('faq');
    }

    /**
     * Render a published CMS page by slug, or 404 if missing / draft.
     */
    public function show(string $slug)
    {
        $db = Database::connect();
        if (! $db->tableExists('cms_pages')) {
            throw PageNotFoundException::forPageNotFound();
        }

        CmsPageDefaults::ensureSlug($slug);

        $model = new CmsPageModel();
        $page  = $model->where('slug', $slug)->where('status', 'published')->first();
        if (! $page) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('public/cms_page', ['page' => $page]);
    }
}
