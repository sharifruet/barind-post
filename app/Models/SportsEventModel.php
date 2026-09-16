<?php

namespace App\Models;

use CodeIgniter\Model;

class SportsEventModel extends Model
{
    protected $table            = 'sports_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'slug', 'custom_url', 'title_bn', 'title_en', 'sport_profile',
        'description_bn', 'banner_image', 'logo_image', 'news_tag_slug',
        'start_date', 'end_date', 'status', 'show_in_nav', 'show_homepage_widget', 'config',
    ];

    public function findBySlugOrUrl(string $segment): ?array
    {
        $event = $this->where('slug', $segment)
            ->orWhere('custom_url', $segment)
            ->first();

        if ($event && is_string($event['config'] ?? null)) {
            $event['config'] = json_decode($event['config'], true) ?: [];
        }

        return $event;
    }

    public function getActiveNavEvents(): array
    {
        return $this->where('status', 'active')
            ->where('show_in_nav', true)
            ->orderBy('start_date', 'DESC')
            ->findAll();
    }

    public function getHomepageWidgetEvents(): array
    {
        return $this->where('status', 'active')
            ->where('show_homepage_widget', true)
            ->orderBy('start_date', 'DESC')
            ->findAll();
    }

    public function decodeConfig(array $event): array
    {
        if (isset($event['config']) && is_string($event['config'])) {
            $event['config'] = json_decode($event['config'], true) ?: [];
        }
        return $event;
    }
}
