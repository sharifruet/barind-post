<?php

/**
 * Sports event helper functions
 */

if (!function_exists('sports_event_url')) {
    function sports_event_url(array $event, string $path = ''): string
    {
        $segment = !empty($event['custom_url']) ? $event['custom_url'] : $event['slug'];
        $base = '/sports/' . trim($segment, '/');
        $path = ltrim($path, '/');
        return $path ? $base . '/' . $path : $base;
    }
}

if (!function_exists('sports_decode_json_field')) {
    function sports_decode_json_field($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (empty($value)) {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
}

if (!function_exists('sports_default_football_data')) {
    function sports_default_football_data(): array
    {
        return [
            'a_score' => null,
            'b_score' => null,
            'a_ht'    => null,
            'b_ht'    => null,
            'a_pen'   => null,
            'b_pen'   => null,
        ];
    }
}

if (!function_exists('sports_default_cricket_data')) {
    function sports_default_cricket_data(string $format = 't20', int $overs = 20): array
    {
        return [
            'format'      => $format,
            'result_text' => '',
            'innings'     => [
                ['team_side' => 'a', 'runs' => null, 'wickets' => null, 'overs' => null],
                ['team_side' => 'b', 'runs' => null, 'wickets' => null, 'overs' => null],
            ],
            'overs_limit' => $overs,
        ];
    }
}

if (!function_exists('sports_format_football_score')) {
    function sports_format_football_score(array $sportData, string $status = 'scheduled'): string
    {
        $a = $sportData['a_score'] ?? null;
        $b = $sportData['b_score'] ?? null;

        if ($a === null && $b === null) {
            return 'vs';
        }

        $score = (int) $a . ' - ' . (int) $b;

        if (in_array($status, ['pen', 'aet'], true)) {
            $aPen = $sportData['a_pen'] ?? null;
            $bPen = $sportData['b_pen'] ?? null;
            if ($aPen !== null && $bPen !== null) {
                $score .= ' (' . (int) $aPen . '-' . (int) $bPen . ' পেন.)';
            }
        }

        return $score;
    }
}

if (!function_exists('sports_format_cricket_scoreline')) {
    function sports_format_cricket_scoreline(array $innings): string
    {
        if (empty($innings)) {
            return '-';
        }
        $lines = [];
        foreach ($innings as $inn) {
            if (($inn['runs'] ?? null) === null) {
                continue;
            }
            $runs = (int) $inn['runs'];
            $wickets = $inn['wickets'] ?? null;
            $overs = $inn['overs'] ?? null;
            $line = $runs . '/' . ($wickets !== null ? (int) $wickets : 0);
            if ($overs !== null && $overs !== '') {
                $line .= ' (' . $overs . ')';
            }
            $lines[] = $line;
        }
        return $lines ? implode(' & ', $lines) : '-';
    }
}

if (!function_exists('sports_match_status_label')) {
    function sports_match_status_label(string $profileKey, string $status): string
    {
        $profiles = config('SportProfiles');
        $profile = $profiles->get($profileKey);
        return $profile['match_statuses'][$status] ?? $status;
    }
}

if (!function_exists('sports_stage_label')) {
    function sports_stage_label(string $profileKey, string $stage): string
    {
        $profiles = config('SportProfiles');
        $profile = $profiles->get($profileKey);
        return $profile['default_stages'][$stage] ?? $stage;
    }
}

if (!function_exists('sports_is_finished')) {
    function sports_is_finished(string $status): bool
    {
        return in_array($status, ['ft', 'pen', 'aet', 'abandoned', 'no_result'], true);
    }
}

if (!function_exists('sports_is_live')) {
    function sports_is_live(string $status): bool
    {
        return in_array($status, ['live', 'ht', 'innings_break'], true);
    }
}
