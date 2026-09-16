<?php

namespace App\Models;

use CodeIgniter\Model;

class SportsMatchEventModel extends Model
{
    protected $table            = 'sports_match_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'match_id', 'event_minute', 'period', 'event_type',
        'participant_id', 'player_name', 'detail', 'sort_order',
    ];

    public function getForMatch(int $matchId): array
    {
        return $this->select('sports_match_events.*, sports_participants.name_bn as team_name, sports_participants.short_code')
            ->join('sports_participants', 'sports_participants.id = sports_match_events.participant_id', 'left')
            ->where('match_id', $matchId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function deleteForMatch(int $matchId): void
    {
        $this->where('match_id', $matchId)->delete();
    }
}
