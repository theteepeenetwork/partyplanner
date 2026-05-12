<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;

class ResetPassword extends BaseController
{
    public function index()
    {
        $token = (string) $this->request->getGet('token');

        return view('reset_password', ['token' => $token]);
    }

    public function submit(): RedirectResponse
    {
        $token     = (string) $this->request->getPost('token');
        $userModel = new UserModel();
        $user      = $userModel->findByPasswordResetToken($token);

        if ($user === null) {
            return redirect()->to('/forgot-password')->with(
                'error',
                'This password reset link is invalid or has expired. Please request a new one.'
            );
        }

        if (empty($user['password_reset_expires_at'])) {
            return redirect()->to('/forgot-password')->with(
                'error',
                'This password reset link is invalid or has expired. Please request a new one.'
            );
        }

        $expires = Time::parse($user['password_reset_expires_at']);
        if ($expires->isBefore(Time::now())) {
            return redirect()->to('/forgot-password')->with(
                'error',
                'This password reset link is invalid or has expired. Please request a new one.'
            );
        }

        $rules = [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/reset-password?token=' . rawurlencode($token))
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel->update((int) $user['id'], [
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);
        $userModel->clearPasswordReset((int) $user['id']);

        return redirect()->to('/login')->with(
            'success',
            'Your password has been updated. You can sign in with your new password.'
        );
    }
}
