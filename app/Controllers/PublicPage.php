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
        return view('public/how_it_works', [
            'pageTitle'       => 'How it works — Partysmith',
            'metaDescription' => 'Tell us about your event once, compare structured quotes from vetted suppliers, and book with payment protected until 48 hours after the day.',
        ]);
    }

    public function contact()
    {
        return view('public/contact', [
            'pageTitle'       => 'Contact — Partysmith',
            'metaDescription' => 'Get in touch with the Partysmith team — we reply within one business day.',
        ]);
    }

    /**
     * Handle the contact form. Validates, records the enquiry to the log
     * (no mail infra wired yet), and returns with a flash confirmation.
     */
    public function submitContact()
    {
        $rules = [
            'email'   => 'required|valid_email',
            'message' => 'required|min_length[10]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->to('/contact')->withInput()->with('errors', $this->validator->getErrors());
        }

        $enquiry = $this->request->getPost(['first_name', 'last_name', 'email', 'i_am', 'topic', 'message']);
        log_message('info', 'Contact enquiry: ' . json_encode($enquiry));

        return redirect()->to('/contact')->with(
            'contactSuccess',
            "Thanks — we've got your message and will reply within one business day."
        );
    }

    public function vendorInfo()
    {
        return view('public/for_vendors', [
            'pageTitle'       => 'For vendors — Partysmith',
            'metaDescription' => 'List your business on Partysmith: qualified enquiries, quote in minutes, and reliable payouts 48 hours after each event.',
        ]);
    }

    public function faq()
    {
        return view('public/faq', [
            'pageTitle'       => 'Help & FAQ — Partysmith',
            'metaDescription' => 'Answers on planning, payments, cancellations and vetting — for hosts and suppliers alike.',
        ]);
    }

    /**
     * Static site map / hub linking every public, account, customer,
     * supplier and admin screen. Not CMS-backed — always available.
     */
    public function sitemap()
    {
        return view('public/sitemap', [
            'pageTitle'       => 'Site map — Partysmith',
            'metaDescription' => 'Every page on Partysmith in one place — public, accounts, customer planning, supplier tools and admin.',
        ]);
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
