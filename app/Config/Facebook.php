<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Posting to the Barind Post Facebook Page from admin → Photo Cards
 * (AdminPhotoCards::postToFacebook). Both values are empty by default, so the
 * "Post to Facebook" button stays disabled until they are set in .env.
 *
 * The n8n pipeline (n8n-workflows/facebook-pipeline.json) posts to the same Page with
 * its own copy of these values (BARIND_FB_PAGE_ID / BARIND_FB_PAGE_TOKEN in .env.n8n).
 */
class Facebook extends BaseConfig
{
    /**
     * Numeric ID of the Facebook Page. Set via `facebook.pageId` in .env.
     */
    public string $pageId = '';

    /**
     * Long-lived Page access token with pages_manage_posts. Set via
     * `facebook.pageToken` in .env. Never commit a real value: anyone holding it can
     * post as the Page.
     */
    public string $pageToken = '';

    /**
     * Graph API version, kept in step with the n8n pipeline.
     */
    public string $graphVersion = 'v21.0';

    public function isConfigured(): bool
    {
        return trim($this->pageId) !== '' && trim($this->pageToken) !== '';
    }
}
