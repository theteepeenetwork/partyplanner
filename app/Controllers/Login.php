<?php namespace App\Controllers;

use App\Models\UserModel;

class Login extends BaseController
{
    public function index()
    {
        return view('login'); // Load the login view
    }

    public function attempt()
    {
        $userModel = new UserModel();
        $usernameOrEmail = $this->request->getVar('login');
        $password = $this->request->getVar('password');

        // Find user by username or email
        $user = $userModel->where('username', $usernameOrEmail)
                          ->orWhere('email', $usernameOrEmail)
                          ->first();

        if ($user && password_verify($password, $user['password'])) {
            session()->set('user_id', $user['id']);
            session()->set('username', $user['username']);
            session()->set('role', $user['role']);

            $redirectUrl = session()->get('redirect_after_login');
            if ($redirectUrl) {
                session()->remove('redirect_after_login');
                return redirect()->to($redirectUrl)->with('success', 'Welcome back, ' . $user['username']);
            }

            if ($user['role'] === 'admin') {
                return redirect()->to('/admin')->with('success', 'Welcome back, ' . $user['name']);
            }

            return redirect()->to('/profile')->with('success', 'Welcome back, ' . $user['name']);
        } else {
            // Authentication failed
            return redirect()->back()->withInput()->with('error', 'Invalid login credentials.');
        }
    }


    public function logout()
    {
        session()->destroy(); // Clear all session data
        return redirect()->to('/'); // Redirect to home page
    }
}
