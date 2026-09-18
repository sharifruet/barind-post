<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * The n8n-facing automation API (app/Controllers/Api/NewsController.php +
 * app/Filters/ApiKeyFilter.php): the paths that must hold without a database —
 * authentication and request validation. The key comes from phpunit.xml.dist
 * (`automation.apiKey`), so no real secret is involved.
 *
 * @internal
 */
final class AutomationApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const KEY = 'phpunit-test-key';

    public function testRequestsWithoutBearerTokenAreRejected(): void
    {
        $result = $this->get('api/v1/categories');

        $result->assertStatus(401);
        $result->assertJSONFragment(['error' => 'Invalid or missing API key']);
    }

    public function testRequestsWithWrongBearerTokenAreRejected(): void
    {
        $result = $this->withHeaders(['Authorization' => 'Bearer not-the-key'])->get('api/v1/categories');

        $result->assertStatus(401);
    }

    public function testExistsRequiresSourceUrl(): void
    {
        $result = $this->withHeaders(['Authorization' => 'Bearer ' . self::KEY])->get('api/v1/news/exists');

        $result->assertStatus(422);
        $result->assertJSONFragment(['error' => 'source_url query parameter is required']);
    }

    public function testCreateRejectsEmptyPayload(): void
    {
        $result = $this->withHeaders(['Authorization' => 'Bearer ' . self::KEY, 'Content-Type' => 'application/json'])
            ->withBodyFormat('json')
            ->post('api/v1/news', []);

        $result->assertStatus(422);
        $json = json_decode($result->getJSON(), true);
        $this->assertSame('Validation failed', $json['error']);
        $this->assertArrayHasKey('title', $json['fields']);
        $this->assertArrayHasKey('content', $json['fields']);
        $this->assertArrayHasKey('category_id', $json['fields']);
    }

    public function testCreateRejectsAnyStatusOtherThanDraftOrPublished(): void
    {
        // "published" is allowed through validation but only honoured when the server's
        // automation.autoPublish is on and every gate passes (see PublishGateTest);
        // anything else is still a hard error rather than a silent downgrade.
        $result = $this->withHeaders(['Authorization' => 'Bearer ' . self::KEY, 'Content-Type' => 'application/json'])
            ->withBodyFormat('json')
            ->post('api/v1/news', [
                'title'       => 'একটি পরীক্ষামূলক শিরোনাম',
                'content'     => str_repeat('পরীক্ষামূলক অনুচ্ছেদ। ', 10),
                'category_id' => 1,
                'status'      => 'archived',
            ]);

        $result->assertStatus(422);
        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('status', $json['fields']);
    }

    public function testCreateRejectsNonArrayTags(): void
    {
        $result = $this->withHeaders(['Authorization' => 'Bearer ' . self::KEY, 'Content-Type' => 'application/json'])
            ->withBodyFormat('json')
            ->post('api/v1/news', [
                'title'       => 'একটি পরীক্ষামূলক শিরোনাম',
                'content'     => str_repeat('পরীক্ষামূলক অনুচ্ছেদ। ', 10),
                'category_id' => 1,
                'tags'        => 'not-an-array',
            ]);

        $result->assertStatus(422);
        $json = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('tags', $json['fields']);
    }
}
