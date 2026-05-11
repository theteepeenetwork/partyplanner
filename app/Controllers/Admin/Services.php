<?php

namespace App\Controllers\Admin;

use App\Libraries\AdminAccountPurge;
use App\Models\CategoryModel;
use App\Models\ServiceModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Services extends BaseAdminController
{
    public function index()
    {
        $vendorId    = (int) $this->request->getGet('vendor_id');
        $categoryId  = (int) $this->request->getGet('category_id');
        $status      = trim((string) $this->request->getGet('status'));
        $showDeleted = (int) $this->request->getGet('deleted') === 1;

        $serviceModel = new ServiceModel();
        $serviceModel->select('services.*, users.name as vendor_name, categories.name as category_name');
        $serviceModel->join('users', 'users.id = services.vendor_id', 'left');
        $serviceModel->join('categories', 'categories.id = services.category_id', 'left');

        if ($vendorId > 0) {
            $serviceModel->where('services.vendor_id', $vendorId);
        }
        if ($categoryId > 0) {
            $serviceModel->where('services.category_id', $categoryId);
        }
        if ($status !== '') {
            $serviceModel->where('services.status', $status);
        }
        if ($showDeleted) {
            $serviceModel->where('services.deleted_at IS NOT NULL', null, false);
        } else {
            $serviceModel->where('services.deleted_at', null);
        }

        $services = $serviceModel->orderBy('services.id', 'DESC')->paginate(25);
        $pager    = $serviceModel->pager;

        $vendors    = (new UserModel())->where('role', 'vendor')->orderBy('name', 'ASC')->findAll();
        $categories = (new CategoryModel())->orderBy('name', 'ASC')->findAll();

        return $this->layout('admin/services/index', [
            'title'        => 'Services',
            'activeNav'    => 'services',
            'services'     => $services,
            'pager'        => $pager,
            'vendors'      => $vendors,
            'categories'   => $categories,
            'vendor_id'    => $vendorId,
            'category_id'  => $categoryId,
            'status'       => $status,
            'show_deleted' => $showDeleted,
        ]);
    }

    public function show(int $id)
    {
        $serviceModel = new ServiceModel();
        $service      = $serviceModel->getServiceWithImages($id);
        if (! $service) {
            throw PageNotFoundException::forPageNotFound();
        }

        $vendor = (new UserModel())->find($service['vendor_id']);

        return $this->layout('admin/services/show', [
            'title'     => 'Service #' . $id,
            'activeNav' => 'services',
            'service'   => $service,
            'vendor'    => $vendor,
        ]);
    }

    public function edit(int $id)
    {
        $serviceModel = new ServiceModel();
        $service      = $serviceModel->find($id);
        if (! $service) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'title'       => 'required|min_length[2]|max_length[255]',
                'description' => 'permit_empty',
                'price'       => 'required|numeric',
                'status'      => 'required|in_list[active,inactive]',
            ];
            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
            }

            $serviceModel->update($id, [
                'title'             => $this->request->getPost('title'),
                'short_description' => $this->request->getPost('short_description'),
                'description'       => $this->request->getPost('description'),
                'price'             => $this->request->getPost('price'),
                'status'            => $this->request->getPost('status'),
                'category_id'       => $this->request->getPost('category_id') ?: null,
            ]);

            return redirect()->to('/admin/services/' . $id)->with('success', 'Service updated.');
        }

        $categories = (new CategoryModel())->orderBy('name', 'ASC')->findAll();

        return $this->layout('admin/services/edit', [
            'title'      => 'Edit service',
            'activeNav'  => 'services',
            'service'    => $service,
            'categories' => $categories,
        ]);
    }

    public function toggleStatus(int $id)
    {
        $serviceModel = new ServiceModel();
        $service      = $serviceModel->find($id);
        if (! $service) {
            return redirect()->back()->with('error', 'Service not found.');
        }

        $next = ($service['status'] === 'active') ? 'inactive' : 'active';
        $serviceModel->update($id, ['status' => $next]);

        return redirect()->back()->with('success', 'Service status updated.');
    }

    public function deleteConfirm(int $id)
    {
        $serviceModel = new ServiceModel();
        $service      = $serviceModel->find($id);
        if (! $service) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->layout('admin/services/delete_confirm', [
            'title'     => 'Remove service',
            'activeNav' => 'services',
            'service'   => $service,
        ]);
    }

    public function delete(int $id)
    {
        $serviceModel = new ServiceModel();
        $service      = $serviceModel->find($id);
        if (! $service) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();
        $db->transStart();
        try {
            (new AdminAccountPurge($db))->purgeServiceFully($id);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Admin service delete: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Deletion failed.');
        }

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'Deletion failed.');
        }

        return redirect()->to('/admin/services')->with('success', 'Service permanently removed.');
    }
}
