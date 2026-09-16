<?php

namespace App\Models;

use CodeIgniter\Model;

class SportsParticipantModel extends Model
{
    protected $table            = 'sports_participants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'name_bn', 'name_en', 'short_code', 'flag_url', 'type',
    ];

    public function getForEvent(int $eventId): array
    {
        return $this->select('sports_participants.*, sports_event_entries.group_name, sports_event_entries.seed, sports_event_entries.id as entry_id')
            ->join('sports_event_entries', 'sports_event_entries.participant_id = sports_participants.id')
            ->where('sports_event_entries.event_id', $eventId)
            ->orderBy('sports_event_entries.group_name', 'ASC')
            ->orderBy('sports_participants.name_bn', 'ASC')
            ->findAll();
    }

    public function getNotInEvent(int $eventId): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('sports_event_entries')
            ->select('participant_id')
            ->where('event_id', $eventId)
            ->get()
            ->getResultArray();
        $ids = array_column($rows, 'participant_id');

        if (empty($ids)) {
            return $this->orderBy('name_bn', 'ASC')->findAll();
        }

        return $this->whereNotIn('id', $ids)
            ->orderBy('name_bn', 'ASC')
            ->findAll();
    }
}
