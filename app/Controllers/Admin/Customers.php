<?php

namespace App\Controllers\Admin;

use App\Libraries\AdminAccountPurge;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Customers extends BaseAdminController
{
    public function index()
    {
        $userModel = new UserModel();
        $q          = trim((string) $this->request->getGet('q'));

        $builder = $userModel->where('role', 'customer');
        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('email', $q)
                ->orLike('username', $q)
                ->groupEnd();
        }

        $customers = $builder->orderBy('id', 'DESC')->paginate(25);
        $pager     = $userModel->pager;

        return $this->layout('admin/customers/index', [
            'title'     => 'Customers',
            'activeNav' => 'customers',
            'customers' => $customers,
            'pager'     => $pager,
            'q'         => $q,
        ]);
    }

    public function show(int $id)
    {
        $user = $this->requireCustomer($id);

        $db = db_connect();

        $events = $db->table('events')->where('user_id', $id)->orderBy('created_at', 'DESC')->get()->getResultArray();

        $bookings = $db->table('bookings')
            ->select('bookings.*, events.title as event_title')
            ->join('events', 'events.id = bookings.event_id', 'left')
            ->where('bookings.user_id', $id)
            ->orderBy('bookings.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $rooms = $db->table('chat_rooms')
            ->select('chat_rooms.*, users.name as vendor_name')
            ->join('users', 'users.id = chat_rooms.vendor_id')
            ->where('chat_rooms.customer_id', $id)
            ->orderBy('chat_rooms.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $this->layout('admin/customers/show', [
            'title'     => 'Customer #' . $id,
            'activeNav' => 'customers',
            'user'      => $user,
            'events'    => $events,
            'bookings'  => $bookings,
            'rooms'     => $rooms,
        ]);
    }

    public function edit(int $id)
    {
        $user = $this->requireCustomer($id);

        if ($this->request->getMethod() === 'post') {
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

            return redirect()->to('/admin/customers/' . $id)->with('success', 'Customer updated.');
        }

        return $this->layout('admin/customers/edit', [
            'title'     => 'Edit customer',
            'activeNav' => 'customers',
            'user'      => $user,
        ]);
    }

    public function deleteConfirm(int $id)
    {
        $user = $this->requireCustomer($id);

        return $this->layout('admin/customers/delete_confirm', [
            'title'     => 'Delete customer',
            'activeNav' => 'customers',
            'user'      => $user,
        ]);
    }

    public function delete(int $id)
    {
        $this->requireCustomer($id);

        $db = db_connect();
        $db->transStart();
        try {
            (new AdminAccountPurge($db))->purgeCustomer($id);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Admin customer delete: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Deletion failed. No changes were applied.');
        }

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'Deletion failed. No changes were applied.');
        }

        return redirect()->to('/admin/customers')->with('success', 'Customer and related data were removed.');
    }

    private function requireCustomer(int $id): array
    {
        $userModel = new UserModel();
        $user      = $userModel->find($id);
        if (! $user || $user['role'] !== 'customer') {
            throw PageNotFoundException::forPageNotFound();
        }

        return $user;
    }
}
