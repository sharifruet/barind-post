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

    /**
     * Optional defense-in-depth IP allowlist for /api/v1/*, checked in addition to
     * the API key (not instead of it). Empty = unrestricted (default), since whether
     * this is needed depends on what the production host's firewall can already do
     * (see N8N_NEWS_AUTOMATION_PLAN.md §8, item 4 — still an open question). Set via
     * `automation.allowedIps` in .env as a comma-separated list, e.g.
     * "203.0.113.10, 203.0.113.11".
     *
     * @var list<string>
     */
    public array $allowedIps = [];

    /**
     * May the pipeline publish, or only ever file drafts?
     *
     * Off by default, and deliberately a *server* setting: whether AI-written copy
     * appears under the masthead is the site owner's decision, not something a
     * workflow can grant itself by sending `"status": "published"`. Even when it is
     * on, an article is only published if it passes every gate in
     * `Api\NewsController::publishGateFailures()`; anything else is filed as a draft
     * for the Incoming queue. Set via `automation.autoPublish` in .env.
     */
    public bool $autoPublish = false;

    /**
     * Minimum body length (words) for auto-publishing. Shorter pieces are still
     * created, just as drafts — a very short article usually means thin extraction.
     * Set via `automation.autoPublishMinWords` in .env.
     */
    public int $autoPublishMinWords = 120;

    public function __construct()
    {
        parent::__construct();

        $this->apiKey = (string) env('automation.apiKey', '');

        $authorId = env('automation.authorId');
        $this->authorId = ($authorId !== null && $authorId !== '') ? (int) $authorId : null;

        $allowedIps = (string) env('automation.allowedIps', '');
        $this->allowedIps = array_values(array_filter(array_map('trim', explode(',', $allowedIps))));

        $this->autoPublish = filter_var(env('automation.autoPublish', false), FILTER_VALIDATE_BOOL);

        $minWords = env('automation.autoPublishMinWords');
        if ($minWords !== null && $minWords !== '') {
            $this->autoPublishMinWords = max(0, (int) $minWords);
        }
    }
}
