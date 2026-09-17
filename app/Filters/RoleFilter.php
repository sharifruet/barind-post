<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Restrict a route to the given roles: `['filter' => 'role:admin']` or
 * `'role:admin,editor,sub-editor'` in app/Config/Routes.php. Runs after
 * AdminAuthFilter, so a session is already guaranteed on admin routes.
 *
 * This replaces the per-method `if ($userRole === 'reporter') { ...redirect }`
 * blocks that used to live in the controllers; the rules are now visible in
 * one place (Routes.php) instead of scattered across ~20 methods.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowed = array_values(array_filter(array_map('trim', (array) $arguments)));
        if ($allowed === []) {
            log_message('error', 'RoleFilter used without roles on ' . $request->getUri()->getPath());

            return;
        }

        if (in_array(session('user_role'), $allowed, true)) {
            return;
        }

        if (AdminAuthFilter::wantsJson($request)) {
            return service('response')->setStatusCode(403)->setJSON(['error' => 'Access denied. Required role: ' . implode(', ', $allowed) . '.']);
        }

        session()->setFlashdata('error', 'You do not have permission to access that area.');

        return redirect()->to('/admin');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
