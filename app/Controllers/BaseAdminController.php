<?php
namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Base for every admin controller.
 *
 * The real gate is App\Filters\AdminAuthFilter, applied to `admin` and
 * `admin/*` in app/Config/Filters.php, with per-route `role:` filters for
 * admin-only areas. This class keeps a defensive check in case a controller
 * is ever reached through a URI outside `admin/*`; it redirects through the
 * framework (RedirectException) instead of header()+exit so the request
 * lifecycle — and the test runner — stay intact.
 */
class BaseAdminController extends Controller
{
    protected $allowedRoles = ['admin', 'editor', 'sub-editor', 'reporter'];

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        $session = session();
        if (
            ! $session->get('logged_in') ||
            ! in_array($session->get('user_role'), $this->allowedRoles, true)
        ) {
            throw new \CodeIgniter\HTTP\Exceptions\RedirectException('/login');
        }
    }
}