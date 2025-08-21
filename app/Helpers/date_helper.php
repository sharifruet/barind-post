<?php

if (!function_exists('convert_to_bangla_date')) {
    /**
     * Convert English date to Bangla format
     * 
     * @param string $dateString The date string to convert
     * @param string $format The output format (default: 'd F, Y')
     * @return string The Bangla formatted date
     */
    function convert_to_bangla_date($dateString, $format = 'd F, Y') {
        $date = new DateTime($dateString);
        
        // Bangla month names
        $banglaMonths = [
            'January' => 'জানুয়ারি',
            'February' => 'ফেব্রুয়ারি',
            'March' => 'মার্চ',
            'April' => 'এপ্রিল',
            'May' => 'মে',
            'June' => 'জুন',
            'July' => 'জুলাই',
            'August' => 'আগস্ট',
            'September' => 'সেপ্টেম্বর',
            'October' => 'অক্টোবর',
            'November' => 'নভেম্বর',
            'December' => 'ডিসেম্বর'
        ];
        
        // Bangla day names (optional)
        $banglaDays = [
            'Monday' => 'সোমবার',
            'Tuesday' => 'মঙ্গলবার',
            'Wednesday' => 'বুধবার',
            'Thursday' => 'বৃহস্পতিবার',
            'Friday' => 'শুক্রবার',
            'Saturday' => 'শনিবার',
            'Sunday' => 'রবিবার'
        ];
        
        // Bangla numbers
        $banglaNumbers = [
            '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
            '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'
        ];
        
        // Convert English to Bangla
        $englishMonth = $date->format('F');
        $englishDay = $date->format('l');
        
        $banglaMonth = $banglaMonths[$englishMonth] ?? $englishMonth;
        $banglaDay = $banglaDays[$englishDay] ?? $englishDay;
        
        // Replace format placeholders
        $banglaDate = $format;
        $banglaDate = str_replace('F', $banglaMonth, $banglaDate);
        $banglaDate = str_replace('l', $banglaDay, $banglaDate);
        
        // Apply the date formatting
        $formattedDate = $date->format($banglaDate);
        
        // Convert numbers to Bangla
        $formattedDate = strtr($formattedDate, $banglaNumbers);
        
        return $formattedDate;
    }
}

if (!function_exists('format_bangla_date')) {
    /**
     * Format date in Bangla with custom options
     * 
     * @param string $dateString The date string to convert
     * @param bool $includeDay Whether to include day name
     * @param bool $includeTime Whether to include time
     * @return string The formatted Bangla date
     */
    function format_bangla_date($dateString, $includeDay = false, $includeTime = false) {
        $date = new DateTime($dateString);
        
        // Bangla month names
        $banglaMonths = [
            'January' => 'জানুয়ারি',
            'February' => 'ফেব্রুয়ারি',
            'March' => 'মার্চ',
            'April' => 'এপ্রিল',
            'May' => 'মে',
            'June' => 'জুন',
            'July' => 'জুলাই',
            'August' => 'আগস্ট',
            'September' => 'সেপ্টেম্বর',
            'October' => 'অক্টোবর',
            'November' => 'নভেম্বর',
            'December' => 'ডিসেম্বর'
        ];
        
        // Bangla day names
        $banglaDays = [
            'Monday' => 'সোমবার',
            'Tuesday' => 'মঙ্গলবার',
            'Wednesday' => 'বুধবার',
            'Thursday' => 'বৃহস্পতিবার',
            'Friday' => 'শুক্রবার',
            'Saturday' => 'শনিবার',
            'Sunday' => 'রবিবার'
        ];
        
        // Bangla numbers
        $banglaNumbers = [
            '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
            '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'
        ];
        
        $englishMonth = $date->format('F');
        $englishDay = $date->format('l');
        
        $banglaMonth = $banglaMonths[$englishMonth] ?? $englishMonth;
        $banglaDay = $banglaDays[$englishDay] ?? $englishDay;
        
        // Convert numbers to Bangla
        $day = strtr($date->format('d'), $banglaNumbers);
        $year = strtr($date->format('Y'), $banglaNumbers);
        
        $formattedDate = $day . ' ' . $banglaMonth . ', ' . $year;
        
        if ($includeDay) {
            $formattedDate = $banglaDay . ', ' . $formattedDate;
        }
        
        if ($includeTime) {
            $hour = strtr($date->format('h'), $banglaNumbers);
            $minute = strtr($date->format('i'), $banglaNumbers);
            $period = $date->format('A') === 'AM' ? 'সকাল' : 'বিকাল';
            $formattedDate .= ' ' . $hour . ':' . $minute . ' ' . $period;
        }
        
        return $formattedDate;
    }
}
