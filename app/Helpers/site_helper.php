<?php

if (! function_exists('published_counts_by_category')) {
    /**
     * [category_id => number of published articles], memoised per request
     * (header, footer and the section action all ask). Returns [] when the
     * database is unavailable, e.g. the DB-free test environment.
     */
    function published_counts_by_category(): array
    {
        static $counts = null;
        if ($counts !== null) {
            return $counts;
        }

        $counts = [];
        try {
            $rows = \Config\Database::connect()->table('news')
                ->select('category_id, COUNT(*) AS n')
                ->where('status', 'published')
                ->groupBy('category_id')
                ->get()->getResultArray();
            foreach ($rows as $row) {
                $counts[(int) $row['category_id']] = (int) $row['n'];
            }
        } catch (\Throwable $e) {
            log_message('error', 'published_counts_by_category failed: ' . $e->getMessage());
        }

        return $counts;
    }
}

if (! function_exists('category_has_enough_content')) {
    /**
     * Whether a section has enough published stories to be linked from the
     * menus and indexed (SiteInfo::$navMinArticles). Empty sections read as
     * "site under construction" to readers and to AdSense reviewers.
     */
    function category_has_enough_content(array $category): bool
    {
        $counts = published_counts_by_category();
        if ($counts === []) {
            return true; // no data (DB down / tests): don't hide the whole menu
        }

        return ($counts[(int) $category['id']] ?? 0) >= config('SiteInfo')->navMinArticles;
    }
}

if (! function_exists('menu_categories')) {
    /** The categories worth linking from the header/footer menus. */
    function menu_categories(array $categories): array
    {
        return array_values(array_filter($categories, 'category_has_enough_content'));
    }
}
