<?php

namespace App\Libraries;

use Throwable;

/**
 * Model Context Protocol (MCP) server that lets AI agents push news, served by App\Controllers\Mcp
 * at POST /mcp (Streamable HTTP transport, stateless, JSON responses — no SSE, no sessions).
 *
 * This class is the protocol: JSON-RPC 2.0 messages in, responses out. The tools are thin
 * wrappers over App\Libraries\NewsIntake, the same code behind /api/v1, so validation, dedup,
 * the auto-publish switch and every publish gate apply exactly as they do for n8n.
 *
 * The local Node server in mcp-server/ exposes the same tools over stdio; keep the tool names,
 * arguments and descriptions of the two in step.
 */
class McpNewsServer
{
    /** Newest first; the first one is offered when a client asks for a version we don't know. */
    public const PROTOCOL_VERSIONS = ['2025-11-25', '2025-06-18', '2025-03-26', '2024-11-05'];

    private const INSTRUCTIONS = <<<'TXT'
        Pushes news articles to the Barind Post website (a Bengali news portal).

        Workflow:
        1. Call list_categories once and pick a category_id (and list_tags if you want tags).
        2. If the story comes from a source page, call check_source_url first: an article with the same
           source_url is rejected as a duplicate.
        3. Call create_news. Articles are filed as drafts for the newsroom unless you pass publish=true AND
           the site allows it (auto-publish on, and the article passes every publish gate). The response
           always says what happened; a draft with publish_gates listed is not an error.

        Writing rules: Bangla copy for language "bn" (the default), no untranslated English clauses; the
        headline must be about what the body says; subtitle is one sentence; key points are 2-4 short lines.
        Never invent facts that are not in the source.
        TXT;

    private NewsIntake $intake;

    public function __construct(?NewsIntake $intake = null)
    {
        $this->intake = $intake ?? new NewsIntake();
    }

    /**
     * Handle one JSON-RPC message. Returns the response, or null when nothing is owed
     * (a notification, or a client's response to a request we never send).
     */
    public function handle(mixed $message): ?array
    {
        if (! is_array($message) || array_is_list($message) || ($message['jsonrpc'] ?? null) !== '2.0') {
            return $this->error(null, -32600, 'Invalid Request: expected a JSON-RPC 2.0 object');
        }

        $hasId = array_key_exists('id', $message);
        $id    = $message['id'] ?? null;

        if (! isset($message['method']) || ! is_string($message['method'])) {
            return $hasId && (array_key_exists('result', $message) || array_key_exists('error', $message))
                ? null
                : $this->error($id, -32600, 'Invalid Request: missing method');
        }

        // notifications/initialized, notifications/cancelled, ...: nothing to do for a stateless server.
        if (! $hasId) {
            return null;
        }

        $params = $message['params'] ?? [];
        if (! is_array($params)) {
            return $this->error($id, -32602, 'Invalid params: expected an object');
        }

        return match ($message['method']) {
            'initialize' => $this->result($id, $this->initialize($params)),
            'ping'       => $this->result($id, new \stdClass()),
            'tools/list' => $this->result($id, ['tools' => $this->tools()]),
            'tools/call' => $this->callTool($id, $params),
            default      => $this->error($id, -32601, 'Method not found: ' . $message['method']),
        };
    }

    private function initialize(array $params): array
    {
        $requested = $params['protocolVersion'] ?? null;

        return [
            'protocolVersion' => in_array($requested, self::PROTOCOL_VERSIONS, true) ? $requested : self::PROTOCOL_VERSIONS[0],
            'capabilities'    => ['tools' => ['listChanged' => false]],
            'serverInfo'      => ['name' => 'barind-post', 'title' => 'Barind Post', 'version' => '1.0.0'],
            'instructions'    => self::INSTRUCTIONS,
        ];
    }

    /**
     * @return list<array>
     */
    public function tools(): array
    {
        $readOnly = ['readOnlyHint' => true, 'openWorldHint' => false];
        $noArgs   = ['type' => 'object', 'properties' => new \stdClass(), 'additionalProperties' => false];

        return [
            [
                'name'        => 'list_categories',
                'title'       => 'List news categories',
                'description' => 'All site sections (id, name, slug). create_news needs one of these ids as category_id.',
                'inputSchema' => $noArgs,
                'annotations' => $readOnly,
            ],
            [
                'name'        => 'list_tags',
                'title'       => 'List tags',
                'description' => 'All existing tags (id, name). create_news accepts tag ids; unknown ids are skipped, tags cannot be created here.',
                'inputSchema' => $noArgs,
                'annotations' => $readOnly,
            ],
            [
                'name'        => 'check_source_url',
                'title'       => 'Check whether a source URL is already on the site',
                'description' => 'Look up an article by the URL of the story it was written from. Call this before writing an article from a source page to avoid duplicate work.',
                'inputSchema' => [
                    'type'                 => 'object',
                    'properties'           => ['source_url' => ['type' => 'string', 'format' => 'uri', 'description' => 'URL of the original source article']],
                    'required'             => ['source_url'],
                    'additionalProperties' => false,
                ],
                'annotations' => $readOnly,
            ],
            [
                'name'        => 'get_news',
                'title'       => 'Get an article',
                'description' => 'Status and basic fields of an article by id (e.g. to see whether a draft has been published by an editor).',
                'inputSchema' => [
                    'type'                 => 'object',
                    'properties'           => ['id' => ['type' => 'integer', 'minimum' => 1]],
                    'required'             => ['id'],
                    'additionalProperties' => false,
                ],
                'annotations' => $readOnly,
            ],
            [
                'name'        => 'list_recent_news',
                'title'       => 'List recently published articles',
                'description' => 'The newest published articles with title, subtitle, key points, section and URL.',
                'inputSchema' => [
                    'type'                 => 'object',
                    'properties'           => ['limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'default' => 20]],
                    'additionalProperties' => false,
                ],
                'annotations' => $readOnly,
            ],
            [
                'name'        => 'create_news',
                'title'       => 'Create a news article',
                'description' => 'Push one article to the live site. It is filed as a draft for the newsroom unless publish=true and the site '
                    . 'allows auto-publishing; see the publish parameter. Rejected as a duplicate if the source_url, or the same title+body, already exists.',
                'inputSchema' => [
                    'type'       => 'object',
                    'properties' => self::CREATE_ARGUMENTS,
                    'required'   => ['title', 'content', 'category_id'],
                    'additionalProperties' => false,
                ],
                'annotations' => ['readOnlyHint' => false, 'destructiveHint' => false, 'idempotentHint' => false, 'openWorldHint' => false],
            ],
        ];
    }

    /**
     * create_news arguments. Deliberately no image_url: pipelines never set the article image,
     * they may only suggest one for an editor to adopt.
     */
    private const CREATE_ARGUMENTS = [
        'title'   => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255, 'description' => 'Headline. Must be about what the body says.'],
        'content' => ['type' => 'string', 'minLength' => 20, 'description' => 'Body as HTML paragraphs (<p>...</p>). Stick to p, h2-h4, strong, em, a, ul/ol/li and blockquote: '
            . 'the site editor strips custom classes and elements the next time the article is saved.'],
        'category_id' => ['type' => 'integer', 'minimum' => 1, 'description' => 'A category id from list_categories'],
        'subtitle'    => ['type' => 'string', 'maxLength' => 255, 'description' => 'One-sentence standfirst; also used as the card excerpt and the SEO/social description'],
        'key_points'  => ['type' => 'array', 'items' => ['type' => 'string', 'minLength' => 1], 'maxItems' => 6,
            'description' => '2-4 short key points, shown as the "এক নজরে" box and on cards without a photo'],
        'language'     => ['type' => 'string', 'enum' => ['bn', 'en'], 'description' => 'Defaults to bn'],
        'source'       => ['type' => 'string', 'maxLength' => 255, 'description' => 'Name of the original outlet, e.g. "প্রথম আলো"'],
        'source_url'   => ['type' => 'string', 'format' => 'uri', 'maxLength' => 500, 'description' => 'URL of the original story. Required for auto-publishing; used for dedup.'],
        'source_title' => ['type' => 'string', 'maxLength' => 500, 'description' => 'The headline as the source published it (used to spot the same event from other outlets)'],
        'dateline'     => ['type' => 'string', 'maxLength' => 255, 'description' => 'e.g. "রাজশাহী"'],
        'suggested_image_url' => ['type' => 'string', 'format' => 'uri', 'maxLength' => 500,
            'description' => 'An image from the source page. It is only a suggestion an editor can adopt; it is never published as-is.'],
        'tags'       => ['type' => 'array', 'items' => ['type' => 'integer', 'minimum' => 1], 'description' => 'Tag ids from list_tags'],
        'importance' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 5, 'description' => '0-5. At or above the server threshold (default 4) the article is featured on the home page.'],
        'featured'   => ['type' => 'boolean', 'description' => 'Set featured explicitly instead of deriving it from importance'],
        'publish'    => ['type' => 'boolean', 'default' => false, 'description' => 'Ask for immediate publication. Honoured only if the site has automation.autoPublish on AND the article passes every '
            . 'publish gate (minimum word count, a subtitle or key points, a source_url, not a suspected duplicate, headline matches '
            . 'body, clean Bangla). Otherwise it is filed as a draft and the reasons are returned in publish_gates.'],
    ];

    private function callTool(mixed $id, array $params): array
    {
        $name = $params['name'] ?? null;
        $args = $params['arguments'] ?? [];

        if (! is_string($name) || ! in_array($name, array_column($this->tools(), 'name'), true)) {
            return $this->error($id, -32602, 'Unknown tool: ' . (is_string($name) ? $name : '(none)'));
        }
        if (! is_array($args) || ($args !== [] && array_is_list($args))) {
            return $this->error($id, -32602, 'Invalid params: arguments must be an object');
        }

        try {
            $result = match ($name) {
                'list_categories'  => $this->listCategories(),
                'list_tags'        => $this->listTags(),
                'check_source_url' => $this->checkSourceUrl($args),
                'get_news'         => $this->getNews($args),
                'list_recent_news' => $this->listRecentNews($args),
                'create_news'      => $this->createNews($args),
            };
        } catch (Throwable $e) {
            // A database hiccup is a failed tool call the model can report, not a protocol error.
            log_message('error', 'MCP tool {tool} failed: {message}', ['tool' => $name, 'message' => $e->getMessage()]);
            $result = $this->toolResult('Internal error on the site; nothing was changed. Try again later.', ['error' => 'internal error'], true);
        }

        return $this->result($id, $result);
    }

    private function listCategories(): array
    {
        [$status, $data] = $this->intake->categories();

        return $status === 200 ? $this->toolResult(count($data['categories']) . ' categories.', $data) : $this->failure($status, $data);
    }

    private function listTags(): array
    {
        [$status, $data] = $this->intake->tags();

        return $status === 200 ? $this->toolResult(count($data['tags']) . ' tags.', $data) : $this->failure($status, $data);
    }

    private function checkSourceUrl(array $args): array
    {
        $url = $args['source_url'] ?? null;
        if (! is_string($url) || ! preg_match('~^https?://~i', $url)) {
            return $this->toolResult('source_url must be an http(s) URL.', ['error' => 'Invalid argument', 'fields' => ['source_url' => 'required http(s) URL']], true);
        }

        [$status, $data] = $this->intake->exists($url);
        if ($status !== 200) {
            return $this->failure($status, $data);
        }

        return $this->toolResult($data['exists'] ? "Already on the site as #{$data['id']} ({$data['status']})." : 'Not on the site yet.', $data);
    }

    private function getNews(array $args): array
    {
        $id = filter_var($args['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            return $this->toolResult('id must be a positive integer.', ['error' => 'Invalid argument', 'fields' => ['id' => 'positive integer required']], true);
        }

        [$status, $data] = $this->intake->show($id);
        if ($status !== 200) {
            return $this->failure($status, $data);
        }
        if ($data['status'] === 'published') {
            $data['url'] = $this->intake->articleUrl($data['slug']);
        }

        return $this->toolResult("#{$data['id']} is {$data['status']}.", $data);
    }

    private function listRecentNews(array $args): array
    {
        $limit = filter_var($args['limit'] ?? 20, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]);
        if ($limit === false) {
            return $this->toolResult('limit must be an integer from 1 to 50.', ['error' => 'Invalid argument', 'fields' => ['limit' => '1-50']], true);
        }

        [$status, $data] = $this->intake->recent($limit);

        return $status === 200 ? $this->toolResult(count($data['articles']) . ' recent articles.', $data) : $this->failure($status, $data);
    }

    private function createNews(array $args): array
    {
        $unknown = array_diff(array_keys($args), array_keys(self::CREATE_ARGUMENTS));
        if ($unknown !== []) {
            return $this->toolResult(
                'Unknown argument(s): ' . implode(', ', $unknown) . '. Nothing was created.'
                    . (in_array('image_url', $unknown, true) ? ' The article image cannot be set here; send suggested_image_url for an editor to adopt.' : ''),
                ['error' => 'Invalid arguments', 'unknown' => array_values($unknown)],
                true
            );
        }

        $publish = filter_var($args['publish'] ?? false, FILTER_VALIDATE_BOOLEAN);
        unset($args['publish']);
        $args['status'] = $publish ? 'published' : 'draft';

        [$status, $data] = $this->intake->create($args);

        // A duplicate is an answer, not a failure: the story is already there, retrying won't help.
        if ($status === 409) {
            $a = $data['article'] ?? [];

            return $this->toolResult("Not created: {$data['error']} (#{$a['id']}, {$a['status']}).", ['outcome' => 'duplicate'] + $data);
        }
        if ($status !== 201) {
            return $this->failure($status, $data);
        }

        $published = $data['status'] === 'published';
        $result    = ['outcome' => $published ? 'published' : 'draft'] + $data
            + ($published ? ['url' => $this->intake->articleUrl($data['slug'])] : [])
            + ['admin_url' => $this->intake->adminEditUrl((int) $data['id'])];

        $summary = $published
            ? "Published #{$data['id']}: {$result['url']}"
            : "Filed #{$data['id']} as a draft for the newsroom (/admin/incoming).";
        if ($publish && ! $published && $data['publish_gates'] !== []) {
            $summary .= ' Not published because: ' . implode('; ', $data['publish_gates']) . '.';
        }
        if ($data['possible_duplicate']) {
            $summary .= " Flagged as a possible duplicate of #{$data['possible_duplicate']['id']}.";
        }
        if ($data['skipped_tags'] !== []) {
            $summary .= ' Unknown tag ids skipped: ' . implode(', ', $data['skipped_tags']) . '.';
        }

        return $this->toolResult($summary, $result);
    }

    /** A non-success NewsIntake answer as an actionable tool error. */
    private function failure(int $status, array $data): array
    {
        $hint = match (true) {
            $status === 404 => 'No article with that id.',
            $status === 422 => 'Validation failed; fix the listed fields and call again.',
            default         => "Server error (HTTP {$status}).",
        };

        return $this->toolResult($hint, ['http_status' => $status] + $data, true);
    }

    /** A tool result: a one-line summary for the model, plus the full data as JSON and as structuredContent. */
    private function toolResult(string $summary, array $data, bool $isError = false): array
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

        return [
            'content'           => [['type' => 'text', 'text' => $summary . "\n\n" . $json]],
            'structuredContent' => $data === [] ? new \stdClass() : $data,
            'isError'           => $isError,
        ];
    }

    private function result(mixed $id, array|object $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    public function error(mixed $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }
}
