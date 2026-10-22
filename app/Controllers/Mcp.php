<?php

namespace App\Controllers;

use App\Libraries\McpNewsServer;

/**
 * POST /mcp — the MCP endpoint AI agents use to push news (Streamable HTTP, stateless).
 *
 * Authenticated by the `apikey` filter, the same Bearer key as /api/v1 (automation.apiKey,
 * plus automation.allowedIps if set). The protocol and the tools live in
 * App\Libraries\McpNewsServer; this controller is the HTTP shell around it.
 */
class Mcp extends BaseController
{
    public function handle()
    {
        // Spec: reject cross-site browser requests (DNS rebinding). Non-browser clients send no Origin.
        $origin = $this->request->getHeaderLine('Origin');
        if ($origin !== '' && ! $this->isOwnOrigin($origin)) {
            return $this->send(403, (new McpNewsServer())->error(null, -32000, 'Forbidden origin'));
        }

        $version = $this->request->getHeaderLine('MCP-Protocol-Version');
        if ($version !== '' && ! in_array($version, McpNewsServer::PROTOCOL_VERSIONS, true)) {
            return $this->send(400, (new McpNewsServer())->error(null, -32600, 'Unsupported MCP-Protocol-Version: ' . $version));
        }

        $message = json_decode((string) $this->request->getBody(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->send(400, (new McpNewsServer())->error(null, -32700, 'Parse error'));
        }

        $server = new McpNewsServer();

        // Batches were dropped from the spec in 2025-06-18 but older clients may still send them.
        if (is_array($message) && $message !== [] && array_is_list($message)) {
            $responses = array_values(array_filter(array_map([$server, 'handle'], $message), static fn ($r) => $r !== null));

            return $responses === [] ? $this->accepted() : $this->send(200, $responses);
        }

        $response = $server->handle($message);

        return $response === null ? $this->accepted() : $this->send(200, $response);
    }

    /** GET (server-sent event stream) and DELETE (session end) are optional; a stateless server offers neither. */
    public function methodNotAllowed()
    {
        return $this->send(405, (new McpNewsServer())->error(null, -32000, 'Method not allowed: use POST'))->setHeader('Allow', 'POST');
    }

    private function isOwnOrigin(string $origin): bool
    {
        $host = parse_url($origin, PHP_URL_HOST);
        if (! is_string($host)) {
            return false;
        }

        $own = array_filter([
            parse_url(base_url(), PHP_URL_HOST),
            parse_url('//' . $this->request->getHeaderLine('Host'), PHP_URL_HOST),
        ]);

        return in_array(strtolower($host), array_map('strtolower', $own), true);
    }

    /** Notifications and client responses: accepted, no body. */
    private function accepted()
    {
        return $this->response->setStatusCode(202)->setBody('');
    }

    private function send(int $status, array $payload)
    {
        // Slashes stay escaped on purpose: the global honeypot `after` filter injects its hidden
        // field before any literal "</form>" in a response body, and article HTML can contain one.
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        return $this->response->setStatusCode($status)->setContentType('application/json')->setBody($body);
    }
}
