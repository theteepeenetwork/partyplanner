<?php

namespace App\Controllers;

use App\Libraries\PasswordResetToken;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;

class ForgotPassword extends BaseController
{
    private const RATE_WINDOW_SECONDS = 900;

    private const RATE_MAX_ATTEMPTS = 5;

    private const SESSION_KEY = 'forgot_password_attempts';

    public function index()
    {
        return view('forgot_password');
    }

    public function send(): RedirectResponse
    {
        $rules = ['email' => 'required|valid_email'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $rateRedirect = $this->consumeForgotPasswordRateSlot();
        if ($rateRedirect !== null) {
            return $rateRedirect;
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

        if ($user !== null) {
            $token   = PasswordResetToken::generate();
            $expires = Time::now()->addMinutes(60);
            $userModel->setPasswordReset((int) $user['id'], $token, $expires->toDateTime());

            $resetUrl = site_url('reset-password?token=' . rawurlencode($token));
            log_message('info', 'Password reset link (dev/MVP, no SMTP): ' . $resetUrl);
        }

        return redirect()->back()->with(
            'success',
            'If an account exists for that email address, you will receive password reset instructions shortly.'
        );
    }

    private function consumeForgotPasswordRateSlot(): ?RedirectResponse
    {
        $now  = time();
        $data = $this->session->get(self::SESSION_KEY);

        if (! is_array($data) || ! isset($data['window_start'], $data['count'])) {
            $data = ['window_start' => $now, 'count' => 0];
        } elseif ($now - (int) $data['window_start'] >= self::RATE_WINDOW_SECONDS) {
            $data = ['window_start' => $now, 'count' => 0];
        }

        if ($data['count'] >= self::RATE_MAX_ATTEMPTS) {
            return redirect()->back()->withInput()->with(
                'error',
                'Too many requests. Please wait a few minutes before trying again.'
            );
        }

        $data['count']++;
        $this->session->set(self::SESSION_KEY, $data);

        return null;
    }
}
