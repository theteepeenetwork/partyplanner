<?php

namespace App\Controllers\Admin;

use App\Libraries\AdminAccountPurge;
use App\Libraries\VendorVetting;
use App\Models\ServiceModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Vendors extends BaseAdminController
{
    public function index()
    {
        $userModel = new UserModel();
        $q         = trim((string) $this->request->getGet('q'));
        $status    = trim((string) $this->request->getGet('status'));
        if (! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = '';
        }

        $builder = $userModel->where('role', 'vendor');
        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('email', $q);
            if ($userModel->db->fieldExists('username', 'users')) {
                $builder->orLike('username', $q);
            }
            $builder->groupEnd();
        }
        if ($status !== '') {
            $builder->where('vendor_status', $status);
        }

        $vendors = $builder->orderBy('id', 'DESC')->paginate(25);
        $pager   = $userModel->pager;

        $pendingCount = (new UserModel())->where('role', 'vendor')->where('vendor_status', 'pending')->countAllResults();

        $serviceModel = new ServiceModel();
        foreach ($vendors as &$vendor) {
            $vendor['services_count'] = $serviceModel->where('vendor_id', $vendor['id'])->countAllResults();
        }
        unset($vendor);

        return $this->layout('admin/vendors/index', [
            'title'        => 'Vendors',
            'activeNav'    => 'vendors',
            'vendors'      => $vendors,
            'pager'        => $pager,
            'q'            => $q,
            'status'       => $status,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function approve(int $id)
    {
        $reasonInput = trim((string) $this->request->getPost('reason'));
        $reason      = $reasonInput !== '' ? $reasonInput : null;

        $vetting = new VendorVetting();
        $ok      = $vetting->approve($id, (int) session()->get('user_id'), $reason);

        if (! $ok) {
            return redirect()->back()->with('error', 'Could not approve that vendor.');
        }

        return redirect()->back()->with('success', 'Vendor approved.');
    }

    public function reject(int $id)
    {
        $reason = trim((string) $this->request->getPost('reason'));

        $vetting = new VendorVetting();
        $ok      = $vetting->reject($id, (int) session()->get('user_id'), $reason);

        if (! $ok) {
            return redirect()->back()->with('error', 'A reason is required to reject a vendor.');
        }

        return redirect()->back()->with('success', 'Vendor rejected.');
    }

    public function show(int $id)
    {
        $user = $this->requireVendor($id);

        $db = db_connect();

        $serviceModel = new ServiceModel();
        $services      = $serviceModel->where('vendor_id', $id)->orderBy('id', 'DESC')->findAll();

        $bookings = $db->table('booking_items')
            ->select('booking_items.*, bookings.id as booking_id, bookings.status as booking_status, bookings.user_id as customer_id, bookings.event_id, bookings.created_at as booking_created, services.title as service_title, customers.name as customer_name')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('users as customers', 'customers.id = bookings.user_id', 'left')
            ->where('services.vendor_id', $id)
            ->orderBy('bookings.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $rooms = $db->table('chat_rooms')
            ->select('chat_rooms.*, users.name as customer_name')
            ->join('users', 'users.id = chat_rooms.customer_id')
            ->where('chat_rooms.vendor_id', $id)
            ->orderBy('chat_rooms.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $this->layout('admin/vendors/show', [
            'title'     => 'Vendor #' . $id,
            'activeNav' => 'vendors',
            'user'      => $user,
            'services'  => $services,
            'bookings'  => $bookings,
            'rooms'     => $rooms,
        ]);
    }

    public function edit(int $id)
    {
        $user = $this->requireVendor($id);

        if ($this->request->is('post')) {
            $rules = [
                'name'     => 'required|min_length[2]|max_length[100]',
                'username' => "required|min_length[2]|max_length[255]|is_unique[users.username,id,{$id}]",
                'email'    => "required|valid_email|max_length[255]|is_unique[users.email,id,{$id}]",
            ];
            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
            }

            $userModel = new UserModel();
            $userModel->update($id, [
                'name'     => $this->request->getPost('name'),
                'username' => $this->request->getPost('username'),
                'email'    => $this->request->getPost('email'),
            ]);

            return redirect()->to('/admin/vendors/' . $id)->with('success', 'Vendor updated.');
        }

        return $this->layout('admin/vendors/edit', [
            'title'     => 'Edit vendor',
            'activeNav' => 'vendors',
            'user'      => $user,
        ]);
    }

    public function deleteConfirm(int $id)
    {
        $user = $this->requireVendor($id);

        return $this->layout('admin/vendors/delete_confirm', [
            'title'     => 'Delete vendor',
            'activeNav' => 'vendors',
            'user'      => $user,
        ]);
    }

    public function delete(int $id)
    {
        $this->requireVendor($id);

        $db = db_connect();
        $db->transStart();
        try {
            (new AdminAccountPurge($db))->purgeVendor($id);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Admin vendor delete: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Deletion failed. No changes were applied.');
        }

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'Deletion failed. No changes were applied.');
        }

        return redirect()->to('/admin/vendors')->with('success', 'Vendor and related data were removed.');
    }

    private function requireVendor(int $id): array
    {
        $userModel = new UserModel();
        $user      = $userModel->find($id);
        if (! $user || $user['role'] !== 'vendor') {
            throw PageNotFoundException::forPageNotFound();
        }

        return $user;
    }
}
