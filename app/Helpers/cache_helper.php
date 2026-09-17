<?php

if (! function_exists('purge_public_cache')) {
    /**
     * Drop every cached public page.
     *
     * Public pages (home, sections, articles, tags) are served from CodeIgniter's
     * page cache for a short TTL (see PublicSite::cachePage calls). Anything that
     * changes what those pages show — publishing, editing, deleting, toggling
     * featured/breaking — calls this so readers never see a stale page for
     * longer than one request. NewsModel fires it automatically after every
     * insert/update/delete; code that writes to `news` through the query
     * builder must call it itself.
     *
     * The whole cache is cleared rather than individual URIs because one story
     * appears on many pages (home, its section, tags, "read next" rails) and the
     * TTL is short enough that rebuilding is cheap.
     */
    function purge_public_cache(): void
    {
        try {
            cache()->clean();
        } catch (\Throwable $e) {
            log_message('error', 'purge_public_cache failed: ' . $e->getMessage());
        }
    }
}
