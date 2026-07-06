<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class VendorAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('user_id')) {
            $session->set('redirect_after_login', current_url());

            return redirect()->to('/login')->with('error', 'Please log in to access the vendor area.');
        }

        // Authorise from the database on every request so approvals/rejections
        // and role promotions take effect without the vendor needing to re-login.
        $userModel = new UserModel();
        $user      = $userModel->find((int) $session->get('user_id'));

        if (! $user) {
            $session->destroy();

            return redirect()->to('/login')->with('error', 'Your session is no longer valid. Please log in again.');
        }

        $role = strtolower(trim((string) ($user['role'] ?? '')));
        if ($role === 'admin') {
            return redirect()->to('/admin');
        }
        if ($role !== 'vendor') {
            return redirect()->to('/profile')->with('error', 'This area is only available to vendor accounts.');
        }

        if ($session->get('role') !== $user['role']) {
            $session->set('role', $user['role']);
        }

        $vendorStatus = $user['vendor_status'] ?? 'pending';
        if ($vendorStatus !== 'approved') {
            // No flash needed — /profile renders the under-review/rejected dashboard state.
            return redirect()->to('/profile');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
