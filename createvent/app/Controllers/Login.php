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
            // Authentication successful
            session()->set('user_id', $user['id']);
            session()->set('username', $user['username']); 
            session()->set('role', $user['role']); 
            // Stop execution to see the output
            return redirect()->to('/')->with('success', 'Welcome back, ' . $user['username']);
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
