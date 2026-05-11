<?php

namespace App\Filters;

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

        if ($session->get('role') !== 'admin') {
            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/admin_forbidden'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
