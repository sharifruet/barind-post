<?php

if (!function_exists('generate_slug')) {
    /**
     * Generate a URL-friendly slug from Bengali text
     * 
     * @param string $text The text to convert to slug
     * @return string The generated slug
     */
    function generate_slug($text) {
        // Convert Bengali text to English transliteration
        $bengali_to_english = [
            'অ' => 'a', 'আ' => 'aa', 'ই' => 'i', 'ঈ' => 'ii', 'উ' => 'u', 'ঊ' => 'uu',
            'ঋ' => 'ri', 'এ' => 'e', 'ঐ' => 'oi', 'ও' => 'o', 'ঔ' => 'ou',
            'ক' => 'k', 'খ' => 'kh', 'গ' => 'g', 'ঘ' => 'gh', 'ঙ' => 'ng',
            'চ' => 'ch', 'ছ' => 'chh', 'জ' => 'j', 'ঝ' => 'jh', 'ঞ' => 'ny',
            'ট' => 't', 'ঠ' => 'th', 'ড' => 'd', 'ঢ' => 'dh', 'ণ' => 'n',
            'ত' => 't', 'থ' => 'th', 'দ' => 'd', 'ধ' => 'dh', 'ন' => 'n',
            'প' => 'p', 'ফ' => 'ph', 'ব' => 'b', 'ভ' => 'bh', 'ম' => 'm',
            'য' => 'y', 'র' => 'r', 'ল' => 'l', 'শ' => 'sh', 'ষ' => 'sh',
            'স' => 's', 'হ' => 'h', 'ড়' => 'r', 'ঢ়' => 'rh', 'য়' => 'y',
            'ৎ' => 't', 'ং' => 'ng', 'ঃ' => 'h', 'ঁ' => 'n',
            'া' => 'a', 'ি' => 'i', 'ী' => 'i', 'ু' => 'u', 'ূ' => 'u',
            'ৃ' => 'ri', 'ে' => 'e', 'ৈ' => 'oi', 'ো' => 'o', 'ৌ' => 'ou',
            '্' => '', 'ঁ' => 'n'
        ];
        
        // Convert Bengali to English
        $text = strtr($text, $bengali_to_english);
        
        // Convert to lowercase
        $text = strtolower($text);
        
        // Replace spaces and special characters with hyphens
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        
        // Remove leading and trailing hyphens
        $text = trim($text, '-');
        
        return $text;
    }
}

if (!function_exists('create_dual_slug')) {
    /**
     * Create both Bengali and English slugs for a news article
     * 
     * @param string $title The news title
     * @param string $custom_slug Optional custom slug
     * @return array Array with 'bengali_slug' and 'english_slug'
     */
    function create_dual_slug($title, $custom_slug = '') {
        $english_slug = $custom_slug ?: generate_slug($title);
        $bengali_slug = $title; // Keep original Bengali title as slug
        
        return [
            'bengali_slug' => $bengali_slug,
            'english_slug' => $english_slug
        ];
    }
} 