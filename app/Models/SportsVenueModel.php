<?php

namespace App\Models;

use CodeIgniter\Model;

class SportsVenueModel extends Model
{
    protected $table            = 'sports_venues';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'name_bn', 'name_en', 'city', 'country', 'capacity', 'image_url',
    ];

    public function getUsedInEvent(int $eventId): array
    {
        return $this->select('sports_venues.*, COUNT(sports_matches.id) as match_count')
            ->join('sports_matches', 'sports_matches.venue_id = sports_venues.id', 'inner')
            ->where('sports_matches.event_id', $eventId)
            ->groupBy('sports_venues.id')
            ->orderBy('sports_venues.name_bn', 'ASC')
            ->findAll();
    }
}
