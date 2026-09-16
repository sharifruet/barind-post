<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class SportProfiles extends BaseConfig
{
    public array $profiles = [
        'football' => [
            'label'           => 'ফুটবল',
            'label_en'        => 'Football',
            'score_view'      => 'football',
            'standing_rule'   => 'football_group',
            'has_groups'      => true,
            'has_standings'   => true,
            'default_stages'  => [
                'group'    => 'গ্রুপ পর্ব',
                'r32'      => 'শেষ বত্রিশ',
                'r16'      => 'শেষ ষোলো',
                'qf'       => 'কোয়ার্টার ফাইনাল',
                'sf'       => 'সেমি ফাইনাল',
                'third'    => 'তৃতীয় স্থান',
                'final'    => 'ফাইনাল',
            ],
            'match_statuses' => [
                'scheduled'  => 'নির্ধারিত',
                'live'       => 'লাইভ',
                'ht'         => 'বিরতি',
                'ft'         => 'শেষ',
                'aet'        => 'অতিরিক্ত সময়',
                'pen'        => 'পেনাল্টি',
                'postponed'  => 'স্থগিত',
                'abandoned'  => 'বাতিল',
            ],
            'timeline_types' => [
                'goal'          => 'গোল',
                'own_goal'      => 'আত্মঘাতী গোল',
                'penalty_goal'  => 'পেনাল্টি গোল',
                'penalty_miss'  => 'পেনাল্টি মিস',
                'yellow_card'   => 'হলুদ কার্ড',
                'red_card'      => 'লাল কার্ড',
                'substitution'  => 'বদলি',
            ],
            'default_config' => [
                'points_win'  => 3,
                'points_draw' => 1,
                'points_loss' => 0,
            ],
        ],
        'cricket_t20' => [
            'label'           => 'ক্রিকেট (টি২০)',
            'label_en'        => 'Cricket (T20)',
            'score_view'      => 'cricket',
            'standing_rule'   => 'cricket_points',
            'has_groups'      => true,
            'has_standings'   => true,
            'default_stages'  => [
                'group'    => 'গ্রুপ পর্ব',
                'sf'       => 'সেমি ফাইনাল',
                'final'    => 'ফাইনাল',
            ],
            'match_statuses' => [
                'scheduled'      => 'নির্ধারিত',
                'live'           => 'লাইভ',
                'innings_break'  => 'ইনিংস বিরতি',
                'ft'             => 'শেষ',
                'abandoned'      => 'বাতিল',
                'no_result'      => 'ফলাফলহীন',
            ],
            'timeline_types' => [
                'wicket'    => 'উইকেট',
                'four'      => 'চার',
                'six'       => 'ছক্কা',
                'milestone' => 'মাইলফলক',
                'run_out'   => 'রান আউট',
            ],
            'default_config' => [
                'points_win'  => 2,
                'points_loss' => 0,
                'points_nr'   => 1,
                'overs'       => 20,
            ],
        ],
        'cricket_odi' => [
            'label'           => 'ক্রিকেট (ওয়ানডে)',
            'label_en'        => 'Cricket (ODI)',
            'score_view'      => 'cricket',
            'standing_rule'   => 'cricket_points',
            'has_groups'      => true,
            'has_standings'   => true,
            'default_stages'  => [
                'group'    => 'গ্রুপ পর্ব',
                'sf'       => 'সেমি ফাইনাল',
                'final'    => 'ফাইনাল',
            ],
            'match_statuses' => [
                'scheduled'      => 'নির্ধারিত',
                'live'           => 'লাইভ',
                'innings_break'  => 'ইনিংস বিরতি',
                'ft'             => 'শেষ',
                'abandoned'      => 'বাতিল',
                'no_result'      => 'ফলাফলহীন',
            ],
            'timeline_types' => [
                'wicket'    => 'উইকেট',
                'four'      => 'চার',
                'six'       => 'ছক্কা',
                'milestone' => 'মাইলফলক',
                'run_out'   => 'রান আউট',
            ],
            'default_config' => [
                'points_win'  => 2,
                'points_loss' => 0,
                'points_nr'   => 1,
                'overs'       => 50,
            ],
        ],
    ];

    public function get(string $profile): ?array
    {
        return $this->profiles[$profile] ?? null;
    }

    public function listForSelect(): array
    {
        $options = [];
        foreach ($this->profiles as $key => $profile) {
            $options[$key] = $profile['label'] . ' (' . $profile['label_en'] . ')';
        }
        return $options;
    }
}
