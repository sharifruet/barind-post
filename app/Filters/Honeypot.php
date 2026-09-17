<?php

namespace App\Filters;

use CodeIgniter\Filters\Honeypot as FrameworkHoneypot;
use CodeIgniter\Honeypot\Exceptions\HoneypotException;
use CodeIgniter\HTTP\RequestInterface;

/**
 * The framework's honeypot filter, but a tripped trap answers with a plain 403
 * and a log line instead of throwing (which renders as a 500 error page and
 * fills the error log with every spam attempt).
 */
class Honeypot extends FrameworkHoneypot
{
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            return parent::before($request, $arguments);
        } catch (HoneypotException $e) {
            log_message('warning', 'Honeypot tripped on {path} from {ip}', [
                'path' => $request->getUri()->getPath(),
                'ip'   => $request->getIPAddress(),
            ]);

            return service('response')->setStatusCode(403)->setBody('Blocked: this submission looks automated.');
        }
    }
}
