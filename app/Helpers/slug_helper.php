<?php

if (!function_exists('generate_slug')) {
    /**
     * Generate a URL-friendly slug from Bengali text
     * 
     * @param string $text The text to convert to slug
     * @return string The generated slug
     */
    function generate_slug($text) {
        // Keep Bengali characters, just clean up for URL
        $text = trim($text);
        
        // Replace spaces and special characters with hyphens
        $text = preg_replace('/[\s\-\_\.\,\!\?\:\;\'\"\(\)\[\]\{\}]+/', '-', $text);
        
        // Remove multiple consecutive hyphens
        $text = preg_replace('/\-+/', '-', $text);
        
        // Remove leading and trailing hyphens
        $text = trim($text, '-');
        
        return $text;
    }
}

if (!function_exists('generate_unique_code')) {
    /**
     * Generate a unique code for news articles
     * Format: BP + 8-character random alphanumeric
     * 
     * @param int $newsId The news ID (optional, for backward compatibility)
     * @param string $publishedAt The published date (optional, for backward compatibility)
     * @return string The unique code
     */
    function generate_unique_code($newsId = null, $publishedAt = null) {
        // Generate 10-character random alphanumeric string (including lowercase)
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = '';
        
        for ($i = 0; $i < 10; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // Generate unique code: 10-character random (no prefix)
        $uniqueCode = $randomString;
        
        return $uniqueCode;
    }
}

if (!function_exists('generate_english_slug')) {
    /**
     * Generate an English slug from Bengali text using transliteration
     * 
     * @param string $bengaliText The Bengali text to convert
     * @return string The English slug
     */
    function generate_english_slug($bengaliText) {
        // Bengali to English transliteration mapping
        $bengaliToEnglish = [
            // Vowels
            'অ' => 'a', 'আ' => 'aa', 'ই' => 'i', 'ঈ' => 'ii', 'উ' => 'u', 'ঊ' => 'uu',
            'ঋ' => 'ri', 'এ' => 'e', 'ঐ' => 'oi', 'ও' => 'o', 'ঔ' => 'ou',
            
            // Consonants
            'ক' => 'k', 'খ' => 'kh', 'গ' => 'g', 'ঘ' => 'gh', 'ঙ' => 'ng',
            'চ' => 'ch', 'ছ' => 'chh', 'জ' => 'j', 'ঝ' => 'jh', 'ঞ' => 'ny',
            'ট' => 't', 'ঠ' => 'th', 'ড' => 'd', 'ঢ' => 'dh', 'ণ' => 'n',
            'ত' => 't', 'থ' => 'th', 'দ' => 'd', 'ধ' => 'dh', 'ন' => 'n',
            'প' => 'p', 'ফ' => 'ph', 'ব' => 'b', 'ভ' => 'bh', 'ম' => 'm',
            'য' => 'y', 'র' => 'r', 'ল' => 'l', 'শ' => 'sh', 'ষ' => 'sh',
            'স' => 's', 'হ' => 'h', 'ড়' => 'r', 'ঢ়' => 'rh', 'য়' => 'y',
            
            // Vowel signs
            'া' => 'a', 'ি' => 'i', 'ী' => 'i', 'ু' => 'u', 'ূ' => 'u',
            'ৃ' => 'ri', 'ে' => 'e', 'ৈ' => 'oi', 'ো' => 'o', 'ৌ' => 'ou',
            '্' => '', // Halant (killer)
            
            // Numbers
            '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
            '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
            
            // Common words mapping for better readability
            'বরেন্দ্র' => 'barendra', 'অঞ্চলে' => 'anchale', 'বৃষ্টির' => 'bristir',
            'জন্য' => 'jonno', 'অপেক্ষা' => 'apeksha', 'রাজশাহী' => 'rajshahi',
            'চাঁপাইনবাবগঞ্জ' => 'chapainawabganj', 'নতুন' => 'notun', 'ডিজিটাল' => 'digital',
            'সেবা' => 'seba', 'চালু' => 'chalu', 'বাংলাদেশ' => 'bangladesh',
            'ক্রিকেট' => 'cricket', 'বিশ্বকাপে' => 'bishwokape', 'দুর্দান্ত' => 'durdanto',
            'দক্ষিণ' => 'dokkhin', 'রাজনীতিতে' => 'rajnitite', 'রাজনৈতিক' => 'rajnoitik',
            'মহল' => 'mohol', 'বিনোদন' => 'binodon', 'জগতে' => 'jagote',
            'নতুন' => 'notun', 'অভিনেত্রীর' => 'obhinetri', 'অঙ্গনে' => 'angone',
            'বিশ্ববিদ্যালয়' => 'bishwabiddaloy', 'উচ্চশিক্ষার' => 'ucchoshikkhar',
            'মান' => 'man', 'মেডিকেল' => 'medical', 'স্বাস্থ্যসেবার' => 'shasthoshebar',
            'আন্তর্জাতিক' => 'antojatik', 'বাণিজ্যিক' => 'banijjik', 'সম্পর্ক' => 'somporko',
            'চেম্বার' => 'chamber', 'ঐতিহ্য' => 'oitihyo', 'রক্ষায়' => 'rokkhay',
            'ঐতিহাসিক' => 'oitihasik', 'পরিবেশ' => 'poribesh', 'সবুজ' => 'shobuj',
            'গড়ে' => 'gore', 'পৌরসভায়' => 'pourshobay', 'গুরুত্বপূর্ণ' => 'guruttopurno',
            'স্থানে' => 'sthane', 'সিসি' => 'cc', 'ক্যামেরা' => 'camera', 'স্থাপন' => 'sthapona',
            'গোদাগাড়ী' => 'godagari'
        ];
        
        // Convert Bengali to English
        $englishText = strtr($bengaliText, $bengaliToEnglish);
        
        // Clean up the text
        $englishText = trim($englishText);
        
        // Replace spaces and special characters with hyphens
        $englishText = preg_replace('/[\s\-\_\.\,\!\?\:\;\'\"\(\)\[\]\{\}]+/', '-', $englishText);
        
        // Remove multiple consecutive hyphens
        $englishText = preg_replace('/\-+/', '-', $englishText);
        
        // Remove leading and trailing hyphens
        $englishText = trim($englishText, '-');
        
        // Convert to lowercase
        $englishText = strtolower($englishText);
        
        return $englishText;
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
        $english_slug = $custom_slug ?: generate_english_slug($title);
        $bengali_slug = generate_slug($title); // Generate proper Bengali slug
        
        return [
            'bengali_slug' => $bengali_slug,
            'english_slug' => $english_slug
        ];
    }
}

if (!function_exists('get_seo_friendly_url')) {
    /**
     * Get SEO-friendly URL for news articles
     * Uses Bengali slug for better local SEO
     * 
     * @param string $slug The news slug
     * @param string $title The news title
     * @return string The SEO-friendly URL
     */
    function get_seo_friendly_url($slug, $title = '') {
        // For Bengali content, use Bengali slug for better SEO
        $bengaliSlug = generate_slug($title ?: $slug);
        
        // Ensure proper URL encoding for Bengali characters
        return urlencode($bengaliSlug);
    }
}

if (!function_exists('get_shareable_url')) {
    /**
     * Get shareable URL that works well on social media
     * Uses English slug for better sharing compatibility
     * 
     * @param string $title The news title
     * @param int $id The news ID
     * @return string The shareable URL
     */
    function get_shareable_url($title, $id) {
        // Use English slug for better social media sharing
        $englishSlug = generate_english_slug($title);
        
        // Fallback to ID if slug is empty
        if (empty($englishSlug)) {
            $englishSlug = 'news-' . $id;
        }
        
        return base_url('news/' . $englishSlug);
    }
}

if (!function_exists('get_unique_code_url')) {
    /**
     * Get clean, shareable URL using unique code
     * 
     * @param int $newsId The news ID
     * @param string $publishedAt The published date
     * @return string The unique code URL
     */
    function get_unique_code_url($newsId, $publishedAt = null) {
        $uniqueCode = generate_unique_code($newsId, $publishedAt);
        return 'http://localhost/news/' . $uniqueCode;
    }
}

if (!function_exists('get_image_url')) {
    /**
     * Get full image URL with base URL if needed
     * 
     * @param string $imageUrl The image URL from database
     * @return string The full image URL
     */
    function get_image_url($imageUrl) {
        if (empty($imageUrl)) {
            return '';
        }
        
        // If it's already a full URL (starts with http), return as-is
        if (strpos($imageUrl, 'http') === 0) {
            return $imageUrl;
        }
        
        // Get base URL from config
        $baseURL = config('App')->baseURL;
        
        // Remove trailing slash from base URL if present
        $baseURL = rtrim($baseURL, '/');
        
        // Remove leading slash from image URL if present
        $imageUrl = ltrim($imageUrl, '/');
        
        // Return full URL
        return $baseURL . '/' . $imageUrl;
    }
} 