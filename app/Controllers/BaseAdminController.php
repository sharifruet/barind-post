<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class BaseAdminController extends Controller
{
    protected $allowedRoles = ['admin', 'editor', 'sub-editor', 'reporter'];

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $session = session();
        if (
            ! $session->get('logged_in') ||
            ! in_array($session->get('user_role'), $this->allowedRoles)
        ) {
            header('Location: /login');
            exit;
        }
    }
} 