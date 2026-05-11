<?php

namespace App\Controllers\Admin;

use App\Models\CmsPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Pages extends BaseAdminController
{
    public function index()
    {
        $model = new CmsPageModel();
        $pages = $model->orderBy('slug', 'ASC')->findAll();

        return $this->layout('admin/pages/index', [
            'title'     => 'Public pages',
            'activeNav' => 'pages',
            'pages'     => $pages,
        ]);
    }

    public function edit(string $slug)
    {
        $model = new CmsPageModel();
        $page  = $model->where('slug', $slug)->first();
        if (! $page) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'post') {
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

            $model->update($page['id'], [
                'title'            => $this->request->getPost('title'),
                'content'          => $this->request->getPost('content'),
                'meta_title'       => $this->request->getPost('meta_title'),
                'meta_description' => $this->request->getPost('meta_description'),
                'status'           => $this->request->getPost('status'),
            ]);

            return redirect()->to('/admin/pages')->with('success', 'Page saved.');
        }

        return $this->layout('admin/pages/edit', [
            'title'     => 'Edit page',
            'activeNav' => 'pages',
            'page'      => $page,
        ]);
    }
}
