<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Guards the /api/v1/* automation routes (see N8N_NEWS_AUTOMATION_PLAN.md).
 * Expects `Authorization: Bearer <automation.apiKey>`. Fails closed: if no key is
 * configured on the server, every request is rejected rather than silently allowed.
 */
class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $configuredKey = (string) config('Automation')->apiKey;

        $providedKey = '';
        if (preg_match('/^Bearer\s+(.+)$/i', (string) $request->getHeaderLine('Authorization'), $matches)) {
            $providedKey = trim($matches[1]);
        }

        if ($configuredKey === '' || $providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 401, 'error' => 'Invalid or missing API key']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed.
    }
}
