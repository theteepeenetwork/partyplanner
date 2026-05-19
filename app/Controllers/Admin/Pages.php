<?php

namespace App\Controllers\Admin;

use App\Libraries\PublicCmsNavHealth;
use App\Models\CmsPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class Pages extends BaseAdminController
{
    /**
     * CMS routes require the `cms_pages` table (see migrations or database_update.sql).
     */
    private function redirectIfCmsTableMissing(): ?ResponseInterface
    {
        if (Database::connect()->tableExists('cms_pages')) {
            return null;
        }

        return redirect()->to('/admin')->with(
            'error',
            'The CMS database table is missing. From the project root run `php spark migrate`, or import `database_update.sql` into MySQL, then reload this page.'
        );
    }

    public function index()
    {
        if ($r = $this->redirectIfCmsTableMissing()) {
            return $r;
        }

        $seeded = CmsPageDefaults::seedMissing();

        $model = new CmsPageModel();
        $pages = $model->orderBy('slug', 'ASC')->findAll();

        if ($seeded > 0) {
            session()->setFlashdata(
                'success',
                $seeded === 1
                    ? 'One default page was added and is ready to edit.'
                    : $seeded . ' default pages were added and are ready to edit.'
            );
        }

        return $this->layout('admin/pages/index', [
            'title'     => 'Public pages',
            'activeNav' => 'pages',
            'pages'     => $pages,
        ]);
    }

    public function edit(string $slug)
    {
        if ($r = $this->redirectIfCmsTableMissing()) {
            return $r;
        }

        $model = new CmsPageModel();
        $page  = $model->where('slug', $slug)->first();

        if (! $page && CmsPageDefaults::ensureSlug($slug)) {
            $page = $model->where('slug', $slug)->first();
        }

        if (! $page) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($this->request->is('post')) {
            $rules = [
                'title'            => 'required|min_length[2]|max_length[255]',
                'content'          => 'permit_empty',
                'meta_title'       => 'permit_empty|max_length[255]',
                'meta_description' => 'permit_empty|max_length[500]',
                'status'           => 'required|in_list[draft,published]',
            ];
            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
            }

            $data = [
                'title'            => $this->request->getPost('title'),
                'content'          => $this->request->getPost('content'),
                'meta_title'       => $this->request->getPost('meta_title'),
                'meta_description' => $this->request->getPost('meta_description'),
                'status'           => $this->request->getPost('status'),
            ];

            $saveModel = new CmsPageModel();

            try {
                $saved = $saveModel->update((int) $page['id'], $data);
            } catch (\Throwable $e) {
                log_message('error', 'Admin CMS page save exception for id {id}: {message}', [
                    'id'      => $page['id'],
                    'message' => $e->getMessage(),
                ]);

                return redirect()->back()->withInput()->with('error', 'The page could not be saved. Please try again.');
            }

            if (! $saved) {
                log_message('error', 'Admin CMS page update returned false for id {id}. Errors: {errors}', [
                    'id'     => $page['id'],
                    'errors' => json_encode($saveModel->errors()),
                ]);

                return redirect()->back()->withInput()->with('error', 'The page could not be saved. Please try again.');
            }

            return redirect()->to('/admin/pages')->with('success', 'Page saved.');
        }

        return $this->layout('admin/pages/edit', [
            'title'     => 'Edit page',
            'activeNav' => 'pages',
            'page'      => $page,
        ]);
    }
}
