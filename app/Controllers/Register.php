<?php namespace App\Controllers;

use App\Models\UserModel;

class Register extends BaseController
{
    public function index()
    {
        return view('register', ['pageTitle' => 'Create your account — Partysmith']);
    }

    public function create()
    {
        $userModel = new UserModel();

        $rules = [
            'name'             => 'required|min_length[2]|max_length[100]',
            'username'         => 'required|min_length[3]|max_length[255]|is_unique[users.username]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'     => $this->request->getVar('name'),
            'username' => $this->request->getVar('username'),
            'email'    => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role'     => 'customer',
        ];

        $userModel->save($data);
        $newUser = $userModel->find($userModel->getInsertID());

        // Auto-login after registration
        session()->set([
            'user_id'  => $newUser['id'],
            'username' => $newUser['username'],
            'role'     => $newUser['role'],
        ]);

        return redirect()->to('/profile')->with('success', 'Welcome! Your account is ready. Start by creating your first event.');
    }
    
    public function success()
    {
        if (!session()->has('success')) {
            return redirect()->to('/register');
        }
    
        $data['success'] = session()->getFlashdata('success');
        return view('register_success', $data);
    }

    public function vendor()
    {
        return view('register_vendor', ['pageTitle' => 'List your business — Partysmith']);
    }

    public function createVendor()
    {
        $userModel = new UserModel();

        $rules = [
            'name'             => 'required|min_length[2]|max_length[100]',
            'username'         => 'required|min_length[3]|max_length[255]|is_unique[users.username]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'     => $this->request->getVar('name'),
            'username' => $this->request->getVar('username'),
            'email'    => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role'     => 'vendor',
        ];

        $userModel->save($data);
        $newUser = $userModel->find($userModel->getInsertID());

        // Auto-login after registration
        session()->set([
            'user_id'  => $newUser['id'],
            'username' => $newUser['username'],
            'role'     => $newUser['role'],
        ]);

        return redirect()->to('/profile')->with('success', 'Welcome! Your vendor account is set up. Start by creating your first service listing.');
    }

    public function vendorSuccess()
    {
        if (!session()->has('success')) {
            return redirect()->to('/register/vendor');
        }
    
        $data['success'] = session()->getFlashdata('success');
        return view('register_vendor_success', $data);
    }
}
