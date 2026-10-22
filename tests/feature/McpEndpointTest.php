<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * POST /mcp over HTTP (App\Controllers\Mcp): authentication, transport rules and the
 * JSON-RPC envelope. Database-free; the tools themselves are covered by McpNewsServerTest.
 *
 * @internal
 */
final class McpEndpointTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const KEY = 'phpunit-test-key';

    private function mcp(mixed $body, array $headers = [])
    {
        return $this->withHeaders(['Authorization' => 'Bearer ' . self::KEY, 'Content-Type' => 'application/json',
            'Accept' => 'application/json, text/event-stream'] + $headers)
            ->withBody(is_string($body) ? $body : json_encode($body))
            ->post('mcp');
    }

    public function testRequiresTheApiKey(): void
    {
        $result = $this->withHeaders(['Content-Type' => 'application/json'])
            ->withBody('{"jsonrpc":"2.0","id":1,"method":"ping"}')
            ->post('mcp');

        $result->assertStatus(401);
    }

    public function testInitializeHandshake(): void
    {
        $result = $this->mcp(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize',
            'params' => ['protocolVersion' => '2025-06-18', 'capabilities' => new stdClass(), 'clientInfo' => ['name' => 't', 'version' => '1']]]);

        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
        $json = json_decode($result->getJSON() ?: $result->response()->getBody(), true);
        $this->assertSame(1, $json['id']);
        $this->assertSame('2025-06-18', $json['result']['protocolVersion']);
    }

    public function testNotificationIsAcceptedWithoutBody(): void
    {
        $result = $this->mcp(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']);

        $result->assertStatus(202);
        $this->assertSame('', (string) $result->response()->getBody());
    }

    public function testToolsListOverHttp(): void
    {
        $json = json_decode($this->mcp(['jsonrpc' => '2.0', 'id' => 'a', 'method' => 'tools/list'])->response()->getBody(), true);

        $this->assertContains('create_news', array_column($json['result']['tools'], 'name'));
    }

    public function testCreateNewsValidationOverHttp(): void
    {
        $json = json_decode($this->mcp(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/call',
            'params' => ['name' => 'create_news', 'arguments' => ['title' => 'ab', 'content' => 'x', 'category_id' => 1]]])->response()->getBody(), true);

        $this->assertTrue($json['result']['isError']);
        $this->assertArrayHasKey('title', $json['result']['structuredContent']['fields']);
    }

    public function testBatchReturnsOnlyTheResponses(): void
    {
        $json = json_decode($this->mcp([
            ['jsonrpc' => '2.0', 'method' => 'notifications/initialized'],
            ['jsonrpc' => '2.0', 'id' => 7, 'method' => 'ping'],
        ])->response()->getBody(), true);

        $this->assertCount(1, $json);
        $this->assertSame(7, $json[0]['id']);
    }

    public function testMalformedJsonIsAParseError(): void
    {
        $result = $this->mcp('{not json');

        $result->assertStatus(400);
        $this->assertSame(-32700, json_decode($result->response()->getBody(), true)['error']['code']);
    }

    public function testForeignBrowserOriginIsRefused(): void
    {
        $this->mcp(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'], ['Origin' => 'https://evil.example'])->assertStatus(403);
        $this->mcp(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'], ['Origin' => rtrim(base_url(), '/')])->assertStatus(200);
    }

    public function testUnsupportedProtocolVersionHeaderIsRefused(): void
    {
        $this->mcp(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'], ['MCP-Protocol-Version' => '1999-01-01'])->assertStatus(400);
    }

    public function testGetIsNotAllowed(): void
    {
        $result = $this->withHeaders(['Authorization' => 'Bearer ' . self::KEY])->get('mcp');

        $result->assertStatus(405);
        $result->assertHeader('Allow', 'POST');
    }
}
