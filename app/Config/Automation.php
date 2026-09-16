<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Settings for the n8n content-automation API (see N8N_NEWS_AUTOMATION_PLAN.md).
 * Both values are unset (empty/null) by default so the /api/v1 routes fail closed
 * until explicitly configured via .env.
 */
class Automation extends BaseConfig
{
    /**
     * Bearer token required on every /api/v1/* request (Authorization: Bearer <key>).
     * Set via `automation.apiKey` in .env. Never commit a real value.
     */
    public string $apiKey;

    /**
     * Existing production users.id used as author_id for articles created via the
     * automation API. Set via `automation.authorId` in .env.
     */
    public ?int $authorId = null;

    public function __construct()
    {
        parent::__construct();

        $this->apiKey = (string) env('automation.apiKey', '');

        $authorId = env('automation.authorId');
        $this->authorId = ($authorId !== null && $authorId !== '') ? (int) $authorId : null;
    }
}
