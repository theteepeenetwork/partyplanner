<?php namespace App\Controllers;

use App\Models\UserModel;

class Register extends BaseController
{
    public function index()
    {
        return view('register'); // Load the registration view
    }

    public function create()
    {
        $userModel = new UserModel();

        // Validation rules for form fields
        $rules = [
            'username' => 'required|min_length[3]|max_length[255]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]'
        ];
    
        if (!$this->validate($rules)) {
            // Validation failed, return to form with errors
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Validation passed, insert user into database
        $data = [
            'name' => $this->request->getVar('name'),
            'username' => $this->request->getVar('username'),
            'email' => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role' => 'customer',
        ];

        $userModel->save($data); // Use the $userModel object to save

        return redirect()->to('/register/success')->with('success', 'Registration successful! You can now login.');
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
        return view('register_vendor');
    }

    public function createVendor()
    {
        $userModel = new UserModel();

        $rules = [
            'username' => 'required|min_length[3]|max_length[255]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]'
        ];
    
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getVar('name'),
            'username' => $this->request->getVar('username'),
            'email' => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role' => 'vendor',
        ];

        $userModel->save($data);

        return redirect()->to('/register/vendor/success')->with('success', 'Your vendor application has been submitted! You can now login.');
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
