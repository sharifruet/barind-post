<?php

namespace App\Models;

use CodeIgniter\Model;

class SportsMatchModel extends Model
{
    protected $table            = 'sports_matches';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'event_id', 'participant_a_id', 'participant_b_id', 'venue_id',
        'kickoff_at', 'stage', 'group_name', 'match_slug', 'status',
        'summary_bn', 'news_id', 'sport_data', 'referee', 'attendance',
    ];

    public function getWithDetails(int $matchId): ?array
    {
        $match = $this->select('sports_matches.*,
                pa.name_bn as team_a_name, pa.name_en as team_a_name_en, pa.short_code as team_a_code, pa.flag_url as team_a_flag,
                pb.name_bn as team_b_name, pb.name_en as team_b_name_en, pb.short_code as team_b_code, pb.flag_url as team_b_flag,
                v.name_bn as venue_name, v.city as venue_city, v.country as venue_country,
                e.slug as event_slug, e.custom_url as event_custom_url, e.title_bn as event_title, e.sport_profile')
            ->join('sports_participants pa', 'pa.id = sports_matches.participant_a_id')
            ->join('sports_participants pb', 'pb.id = sports_matches.participant_b_id')
            ->join('sports_venues v', 'v.id = sports_matches.venue_id', 'left')
            ->join('sports_events e', 'e.id = sports_matches.event_id')
            ->where('sports_matches.id', $matchId)
            ->first();

        if ($match) {
            $match['sport_data'] = sports_decode_json_field($match['sport_data'] ?? null);
        }

        return $match;
    }

    public function getForEvent(int $eventId, array $filters = []): array
    {
        $builder = $this->select('sports_matches.*,
                pa.name_bn as team_a_name, pa.short_code as team_a_code, pa.flag_url as team_a_flag,
                pb.name_bn as team_b_name, pb.short_code as team_b_code, pb.flag_url as team_b_flag,
                v.name_bn as venue_name, v.city as venue_city')
            ->join('sports_participants pa', 'pa.id = sports_matches.participant_a_id')
            ->join('sports_participants pb', 'pb.id = sports_matches.participant_b_id')
            ->join('sports_venues v', 'v.id = sports_matches.venue_id', 'left')
            ->where('sports_matches.event_id', $eventId);

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'finished') {
                $builder->whereIn('sports_matches.status', ['ft', 'pen', 'aet', 'abandoned', 'no_result']);
            } elseif ($filters['status'] === 'upcoming') {
                $builder->where('sports_matches.status', 'scheduled');
            } elseif ($filters['status'] === 'live') {
                $builder->whereIn('sports_matches.status', ['live', 'ht', 'innings_break']);
            } else {
                $builder->where('sports_matches.status', $filters['status']);
            }
        }

        if (!empty($filters['group_name'])) {
            $builder->where('sports_matches.group_name', $filters['group_name']);
        }

        if (!empty($filters['stage'])) {
            $builder->where('sports_matches.stage', $filters['stage']);
        }

        if (!empty($filters['date'])) {
            $builder->where('DATE(sports_matches.kickoff_at)', $filters['date']);
        }

        $matches = $builder->orderBy('sports_matches.kickoff_at', 'ASC')->findAll();

        foreach ($matches as &$match) {
            $match['sport_data'] = sports_decode_json_field($match['sport_data'] ?? null);
        }

        return $matches;
    }

    public function findByEventAndSlug(int $eventId, string $slug): ?array
    {
        $match = $this->where('event_id', $eventId)->where('match_slug', $slug)->first();
        if ($match) {
            return $this->getWithDetails((int) $match['id']);
        }
        return null;
    }

    public function getTodayMatches(int $eventId): array
    {
        return $this->getForEvent($eventId, ['date' => date('Y-m-d')]);
    }

    public function getLiveMatches(int $eventId): array
    {
        return $this->getForEvent($eventId, ['status' => 'live']);
    }

    public function getRecentResults(int $eventId, int $limit = 10): array
    {
        $matches = $this->getForEvent($eventId, ['status' => 'finished']);
        return array_slice(array_reverse($matches), 0, $limit);
    }
}
