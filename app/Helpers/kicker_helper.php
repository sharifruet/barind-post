<?php

if (!function_exists('display_kicker')) {
    /**
     * Display kicker with custom styling
     * 
     * @param array $news The news array containing kicker and kicker_color
     * @param string $class Additional CSS classes
     * @return string HTML for kicker display
     */
    function display_kicker($news, $class = '') {
        if (empty($news['kicker'])) {
            return '';
        }
        
        $color = $news['kicker_color'] ?? '#dc3545';
        $kicker = esc($news['kicker']);
        
        return '<div class="kicker ' . esc($class) . '" style="color: ' . esc($color) . '; font-size: 0.9em; font-weight: bold; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">' . $kicker . '</div>';
    }
}

if (!function_exists('get_kicker_style')) {
    /**
     * Get inline CSS style for kicker
     * 
     * @param array $news The news array containing kicker_color
     * @return string Inline CSS style
     */
    function get_kicker_style($news) {
        if (empty($news['kicker'])) {
            return '';
        }
        
        $color = $news['kicker_color'] ?? '#dc3545';
        return 'color: ' . esc($color) . '; font-size: 0.9em; font-weight: bold; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;';
    }
}
