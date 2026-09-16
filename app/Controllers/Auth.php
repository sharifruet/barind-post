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
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $user = $model->where('email', $email)->first();
        if ($user && password_verify($password, $user['password'])) {
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

    public function logout(): RedirectResponse
    {
        session()->destroy();
        return redirect()->to('/login');
    }
} 