<?php

namespace App\Models;

use CodeIgniter\Model;

class SportsEventEntryModel extends Model
{
    protected $table            = 'sports_event_entries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'event_id', 'participant_id', 'group_name', 'seed',
    ];

    public function getGroupsForEvent(int $eventId): array
    {
        $rows = $this->select('group_name')
            ->where('event_id', $eventId)
            ->where('group_name IS NOT NULL')
            ->where('group_name !=', '')
            ->groupBy('group_name')
            ->orderBy('group_name', 'ASC')
            ->findAll();

        return array_column($rows, 'group_name');
    }
}
