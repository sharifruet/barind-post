<?php
namespace App\Controllers;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    public function login()
    {
        helper(['form']);
        return view('auth/login');
    }

    public function attemptLogin()
    {

        helper(['form']);
        $session = session();
        $model = new UserModel();
        $email = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        // Brute-force protection: 5 attempts per minute per IP+email, 30 per
        // minute per IP (so one IP cannot spray many accounts either).
        $throttler = $this->loginThrottler();
        $ip        = $this->request->getIPAddress();
        $pairKey   = 'login-' . md5($ip . '|' . mb_strtolower(trim($email)));
        $ipKey     = 'login-ip-' . md5($ip);
        if ($throttler->check($ipKey, 30, MINUTE) === false || $throttler->check($pairKey, 5, MINUTE) === false) {
            log_message('warning', 'Login throttled for {ip} ({email})', ['ip' => $ip, 'email' => $email]);

            return redirect()->back()->with('error', 'Too many login attempts. Please wait a minute and try again.');
        }

        $user = $model->where('email', $email)->first();
        if ($user && password_verify($password, $user['password'])) {
            $throttler->remove($pairKey);
            $session->set([
                'user_id' => $user['id'],
                'user_name' => $user['name'],
                'user_role' => $user['role'],
                'logged_in' => true
            ]);
            return redirect()->to('/admin');
        } else {
            return redirect()->back()->with('error', 'Invalid login credentials');
        }
    }

    /**
     * Throttle buckets live in their own directory, not in the page cache:
     * purge_public_cache() empties writable/cache on every news change and
     * must not be able to reset login attempt counters.
     */
    private function loginThrottler(): \CodeIgniter\Throttle\Throttler
    {
        $path = WRITEPATH . 'throttle/';
        if (! is_dir($path)) {
            @mkdir($path, 0775, true);
        }

        $config = clone config('Cache');
        $config->file = ['storePath' => $path, 'mode' => 0640];
        $handler = new \CodeIgniter\Cache\Handlers\FileHandler($config);
        $handler->initialize();

        return new \CodeIgniter\Throttle\Throttler($handler);
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();
        return redirect()->to('/login');
    }
} 