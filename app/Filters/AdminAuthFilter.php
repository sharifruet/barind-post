<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Gate for the whole admin area (applied to `admin` and `admin/*` in
 * app/Config/Filters.php): a logged-in session with one of the newsroom roles.
 * Finer-grained restrictions are RoleFilter on individual routes.
 */
class AdminAuthFilter implements FilterInterface
{
    public const ROLES = ['admin', 'editor', 'sub-editor', 'reporter'];

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if ($session->get('logged_in') && in_array($session->get('user_role'), self::ROLES, true)) {
            return;
        }

        if (self::wantsJson($request)) {
            return service('response')->setStatusCode(401)->setJSON(['error' => 'Authentication required']);
        }

        return redirect()->to('/login');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    /**
     * AJAX callers (fetch/XHR posting or expecting JSON) get a JSON error
     * instead of a redirect to an HTML page they cannot use.
     */
    public static function wantsJson(RequestInterface $request): bool
    {
        return (method_exists($request, 'isAJAX') && $request->isAJAX())
            || str_contains($request->getHeaderLine('Accept'), 'application/json')
            || str_contains($request->getHeaderLine('Content-Type'), 'application/json');
    }
}
