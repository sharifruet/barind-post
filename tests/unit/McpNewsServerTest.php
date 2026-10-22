<?php

use App\Libraries\McpNewsServer;
use App\Libraries\NewsIntake;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The MCP protocol layer and tool mapping (App\Libraries\McpNewsServer), database-free:
 * NewsIntake is replaced by a fake, since its own behaviour is covered by AutomationApiTest
 * and PublishGateTest.
 *
 * @internal
 */
final class McpNewsServerTest extends CIUnitTestCase
{
    private McpFakeIntake $intake;
    private McpNewsServer $server;

    protected function setUp(): void
    {
        parent::setUp();
        $this->intake = new McpFakeIntake();
        $this->server = new McpNewsServer($this->intake);
    }

    private function call(string $method, array $params = [], int $id = 1): ?array
    {
        return $this->server->handle(['jsonrpc' => '2.0', 'id' => $id, 'method' => $method, 'params' => $params]);
    }

    private function tool(string $name, array $arguments = []): array
    {
        return $this->call('tools/call', ['name' => $name, 'arguments' => $arguments])['result'];
    }

    public function testInitializeEchoesASupportedProtocolVersion(): void
    {
        $result = $this->call('initialize', ['protocolVersion' => '2025-06-18', 'capabilities' => [], 'clientInfo' => ['name' => 't', 'version' => '1']])['result'];

        $this->assertSame('2025-06-18', $result['protocolVersion']);
        $this->assertSame('barind-post', $result['serverInfo']['name']);
        $this->assertArrayHasKey('tools', $result['capabilities']);
        $this->assertStringContainsString('create_news', $result['instructions']);
    }

    public function testInitializeOffersTheLatestVersionForAnUnknownOne(): void
    {
        $result = $this->call('initialize', ['protocolVersion' => '1999-01-01'])['result'];

        $this->assertSame(McpNewsServer::PROTOCOL_VERSIONS[0], $result['protocolVersion']);
    }

    public function testNotificationsGetNoResponse(): void
    {
        $this->assertNull($this->server->handle(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']));
    }

    public function testPingAnswersWithAnEmptyObject(): void
    {
        $this->assertSame('{}', json_encode($this->call('ping')['result']));
    }

    public function testProtocolErrors(): void
    {
        $this->assertSame(-32600, $this->server->handle(['id' => 1, 'method' => 'ping'])['error']['code']);
        $this->assertSame(-32600, $this->server->handle('nonsense')['error']['code']);
        $this->assertSame(-32601, $this->call('resources/list')['error']['code']);
        $this->assertSame(-32602, $this->call('tools/call', ['name' => 'drop_database'])['error']['code']);
    }

    public function testToolListMatchesTheLocalNodeServer(): void
    {
        $tools = $this->call('tools/list')['result']['tools'];

        $this->assertSame(
            ['list_categories', 'list_tags', 'check_source_url', 'get_news', 'list_recent_news', 'create_news'],
            array_column($tools, 'name')
        );
        // A tool without arguments must still declare `properties` as a JSON object, not [].
        $this->assertSame('{"type":"object","properties":{},"additionalProperties":false}', json_encode($tools[0]['inputSchema']));

        $create = $tools[5]['inputSchema'];
        $this->assertSame(['title', 'content', 'category_id'], $create['required']);
        $this->assertArrayNotHasKey('image_url', $create['properties']);
    }

    public function testListCategories(): void
    {
        $result = $this->tool('list_categories');

        $this->assertFalse($result['isError']);
        $this->assertStringStartsWith('1 categories.', $result['content'][0]['text']);
        $this->assertSame('রাজশাহী', $result['structuredContent']['categories'][0]['name']);
    }

    public function testCreateNewsFilesADraftByDefault(): void
    {
        $this->intake->createResponse = [201, ['id' => 41, 'slug' => 'abc', 'status' => 'draft', 'skipped_tags' => [], 'possible_duplicate' => null, 'publish_gates' => []]];

        $result = $this->tool('create_news', ['title' => 'শিরোনাম', 'content' => '<p>অনুচ্ছেদ</p>', 'category_id' => 3, 'key_points' => ['এক', 'দুই']]);

        $this->assertSame('draft', $this->intake->created[0]['status']);
        $this->assertSame(['এক', 'দুই'], $this->intake->created[0]['key_points']);
        $this->assertArrayNotHasKey('publish', $this->intake->created[0]);
        $this->assertFalse($result['isError']);
        $this->assertSame('draft', $result['structuredContent']['outcome']);
        $this->assertSame(rtrim(base_url(), '/') . '/admin/news/edit/41', $result['structuredContent']['admin_url']);
        $this->assertArrayNotHasKey('url', $result['structuredContent']);
    }

    public function testPublishIsARequestAndFailedGatesAreExplained(): void
    {
        $this->intake->createResponse = [201, ['id' => 42, 'slug' => 'def', 'status' => 'draft', 'skipped_tags' => [], 'possible_duplicate' => null, 'publish_gates' => ['body is 9 words, minimum is 120']]];

        $result = $this->tool('create_news', ['title' => 'শিরোনাম', 'content' => '<p>অনুচ্ছেদ</p>', 'category_id' => 3, 'publish' => true]);

        $this->assertSame('published', $this->intake->created[0]['status']);
        $this->assertSame('draft', $result['structuredContent']['outcome']);
        $this->assertStringContainsString('Not published because: body is 9 words', $result['content'][0]['text']);
    }

    public function testAPublishedArticleReturnsItsPublicUrl(): void
    {
        $this->intake->createResponse = [201, ['id' => 43, 'slug' => 'ghi', 'status' => 'published', 'skipped_tags' => [], 'possible_duplicate' => null, 'publish_gates' => []]];

        $result = $this->tool('create_news', ['title' => 'শিরোনাম', 'content' => '<p>অনুচ্ছেদ</p>', 'category_id' => 3, 'publish' => true]);

        $this->assertSame('published', $result['structuredContent']['outcome']);
        $this->assertSame(rtrim(base_url(), '/') . '/news/ghi', $result['structuredContent']['url']);
    }

    public function testADuplicateIsAnOutcomeNotAnError(): void
    {
        $this->intake->createResponse = [409, ['error' => 'An article with this source_url already exists', 'article' => ['id' => 7, 'slug' => 'x', 'status' => 'published']]];

        $result = $this->tool('create_news', ['title' => 'শিরোনাম', 'content' => '<p>অনুচ্ছেদ</p>', 'category_id' => 3]);

        $this->assertFalse($result['isError']);
        $this->assertSame('duplicate', $result['structuredContent']['outcome']);
    }

    public function testValidationFailuresAreToolErrorsWithTheFields(): void
    {
        $this->intake->createResponse = [422, ['error' => 'Validation failed', 'fields' => ['category_id' => 'Category does not exist.']]];

        $result = $this->tool('create_news', ['title' => 'শিরোনাম', 'content' => '<p>অনুচ্ছেদ</p>', 'category_id' => 999]);

        $this->assertTrue($result['isError']);
        $this->assertSame('Category does not exist.', $result['structuredContent']['fields']['category_id']);
    }

    public function testTheArticleImageCannotBeSetThroughMcp(): void
    {
        $result = $this->tool('create_news', ['title' => 'শিরোনাম', 'content' => '<p>অনুচ্ছেদ</p>', 'category_id' => 3, 'image_url' => 'https://e.test/a.jpg']);

        $this->assertTrue($result['isError']);
        $this->assertStringContainsString('suggested_image_url', $result['content'][0]['text']);
        $this->assertSame([], $this->intake->created);
    }

    public function testRealValidationRunsWithoutADatabase(): void
    {
        // The real NewsIntake rejects bad input before touching the database.
        $server = new McpNewsServer(new NewsIntake());
        $result = $server->handle(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call',
            'params' => ['name' => 'create_news', 'arguments' => ['title' => 'ab', 'content' => 'short', 'category_id' => 3]]])['result'];

        $this->assertTrue($result['isError']);
        $this->assertArrayHasKey('title', $result['structuredContent']['fields']);
        $this->assertArrayHasKey('content', $result['structuredContent']['fields']);
    }

    public function testGetNewsDistinguishesMissingArticlesAndBadIds(): void
    {
        $this->assertSame('#5 is published.', strtok($this->tool('get_news', ['id' => 5])['content'][0]['text'], "\n"));
        $this->assertSame(rtrim(base_url(), '/') . '/news/five', $this->tool('get_news', ['id' => 5])['structuredContent']['url']);
        $this->assertStringStartsWith('No article with that id.', $this->tool('get_news', ['id' => 6])['content'][0]['text']);
        $this->assertTrue($this->tool('get_news', ['id' => 'abc'])['isError']);
    }

    public function testArgumentChecksOnReadTools(): void
    {
        $this->assertTrue($this->tool('check_source_url', [])['isError']);
        $this->assertTrue($this->tool('list_recent_news', ['limit' => 500])['isError']);
        $this->assertFalse($this->tool('check_source_url', ['source_url' => 'https://e.test/a'])['isError']);
    }

    public function testAnExceptionBecomesAToolErrorNotAProtocolError(): void
    {
        $this->intake->explode = true;

        $response = $this->call('tools/call', ['name' => 'list_tags', 'arguments' => []]);

        $this->assertArrayHasKey('result', $response);
        $this->assertTrue($response['result']['isError']);
        $this->assertStringNotContainsString('boom', $response['result']['content'][0]['text']);
    }
}

/** Stands in for the database-backed NewsIntake. */
final class McpFakeIntake extends NewsIntake
{
    /** @var list<array> */
    public array $created = [];
    public array $createResponse = [500, ['error' => 'not set']];
    public bool $explode = false;

    public function categories(): array
    {
        return [200, ['categories' => [['id' => '3', 'name' => 'রাজশাহী', 'slug' => 'rajshahi']]]];
    }

    public function tags(): array
    {
        if ($this->explode) {
            throw new RuntimeException('boom');
        }

        return [200, ['tags' => []]];
    }

    public function exists(mixed $sourceUrl): array
    {
        return [200, ['exists' => false]];
    }

    public function show(int $id): array
    {
        return $id === 5
            ? [200, ['id' => '5', 'slug' => 'five', 'status' => 'published', 'title' => 't']]
            : [404, ['error' => 'Not found']];
    }

    public function create(array $payload): array
    {
        $this->created[] = $payload;

        return $this->createResponse;
    }
}
