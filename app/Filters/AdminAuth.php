<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('user_id')) {
            $session->set('redirect_after_login', current_url());

            return redirect()->to('/login')->with('error', 'Please log in to access the admin area.');
        }

        // Authorise from the database so promotions to admin work without fighting a stale session role.
        $userModel = new UserModel();
        $user      = $userModel->find((int) $session->get('user_id'));

        if (! $user) {
            $session->destroy();

            return redirect()->to('/login')->with('error', 'Your session is no longer valid. Please log in again.');
        }

        $role = strtolower(trim((string) ($user['role'] ?? '')));
        if ($role !== 'admin') {
            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/admin_forbidden'));
        }

        if ($session->get('role') !== $user['role']) {
            $session->set('role', $user['role']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
