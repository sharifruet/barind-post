<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\NewsIntake;

/**
 * Automation API used by the local n8n instance to create news articles.
 * See N8N_NEWS_AUTOMATION_PLAN.md for the design and the ApiKeyFilter that guards
 * this whole controller via the `api/v1` route group.
 *
 * The logic lives in App\Libraries\NewsIntake, shared with the MCP endpoint (App\Controllers\Mcp);
 * this controller only maps query/body to it and its result to a JSON response.
 */
class NewsController extends BaseController
{
    public function exists()
    {
        return $this->json((new NewsIntake())->exists($this->request->getGet('source_url')));
    }

    public function show($id)
    {
        return $this->json((new NewsIntake())->show((int) $id));
    }

    /** Recent published articles with the card fields the Facebook pipeline needs. */
    public function recent()
    {
        return $this->json((new NewsIntake())->recent((int) ($this->request->getGet('limit') ?? 20)));
    }

    public function categories()
    {
        return $this->json((new NewsIntake())->categories());
    }

    public function tags()
    {
        return $this->json((new NewsIntake())->tags());
    }

    public function create()
    {
        $payload = $this->request->getJSON(true);

        return $this->json((new NewsIntake())->create(is_array($payload) ? $payload : []));
    }

    /**
     * @param array{0:int, 1:array} $result
     */
    private function json(array $result)
    {
        [$status, $body] = $result;

        return $this->response->setStatusCode($status)->setJSON($body);
    }
}
