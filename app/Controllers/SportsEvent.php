<?php

namespace App\Controllers;

use App\Models\SportsEventModel;
use App\Models\SportsMatchModel;
use App\Models\SportsParticipantModel;
use App\Models\SportsMatchEventModel;
use App\Models\SportsEventEntryModel;
use App\Models\NewsModel;
use App\Models\CategoryModel;
use App\Libraries\StandingCalculator;
use CodeIgniter\Controller;

class SportsEvent extends Controller
{
    protected SportsEventModel $eventModel;
    protected SportsMatchModel $matchModel;
    protected SportsParticipantModel $participantModel;
    protected SportsMatchEventModel $matchEventModel;
    protected SportsEventEntryModel $entryModel;

    public function __construct()
    {
        $this->eventModel = new SportsEventModel();
        $this->matchModel = new SportsMatchModel();
        $this->participantModel = new SportsParticipantModel();
        $this->matchEventModel = new SportsMatchEventModel();
        $this->entryModel = new SportsEventEntryModel();
        helper(['sports_event', 'slug']);
        header('Content-Type: text/html; charset=UTF-8');
    }

    protected function resolveEvent(string $segment): array
    {
        $event = $this->eventModel->findBySlugOrUrl($segment);
        if (!$event || $event['status'] === 'draft') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return $this->eventModel->decodeConfig($event);
    }

    protected function getProfile(array $event): array
    {
        $profiles = config('SportProfiles');
        return $profiles->get($event['sport_profile']) ?? $profiles->get('football');
    }

    protected function baseData(array $event, string $activeTab = 'hub'): array
    {
        $categoryModel = new CategoryModel();
        return [
            'event'      => $event,
            'profile'    => $this->getProfile($event),
            'activeTab'  => $activeTab,
            'categories' => $categoryModel->findAll(),
            'title'      => $event['title_bn'] . ' - বারিন্দ পোস্ট',
        ];
    }

    public function hub(string $segment)
    {
        $event = $this->resolveEvent($segment);
        $data = $this->baseData($event, 'hub');
        $data['todayMatches'] = $this->matchModel->getTodayMatches((int) $event['id']);
        $data['liveMatches'] = $this->matchModel->getLiveMatches((int) $event['id']);
        $data['recentResults'] = $this->matchModel->getRecentResults((int) $event['id'], 6);
        $data['upcoming'] = array_slice($this->matchModel->getForEvent((int) $event['id'], ['status' => 'upcoming']), 0, 6);
        $data['news'] = $this->getEventNews($event, 8);

        $config = $event['config'] ?: ($data['profile']['default_config'] ?? []);
        $calculator = new StandingCalculator();
        $groups = $this->entryModel->getGroupsForEvent((int) $event['id']);
        if (!empty($groups)) {
            $firstGroup = $groups[0];
            $data['standingsPreview'] = $calculator->calculate($data['profile']['standing_rule'], (int) $event['id'], $firstGroup, $config);
            $data['standingsGroup'] = $firstGroup;
        }

        return view('sports_event/hub', $data);
    }

    public function fixtures(string $segment)
    {
        $event = $this->resolveEvent($segment);
        $data = $this->baseData($event, 'fixtures');
        $data['matches'] = $this->matchModel->getForEvent((int) $event['id']);
        $data['groups'] = $this->entryModel->getGroupsForEvent((int) $event['id']);
        return view('sports_event/fixtures', $data);
    }

    public function results(string $segment)
    {
        $event = $this->resolveEvent($segment);
        $data = $this->baseData($event, 'results');
        $data['matches'] = $this->matchModel->getForEvent((int) $event['id'], ['status' => 'finished']);
        return view('sports_event/results', $data);
    }

    public function standings(string $segment)
    {
        $event = $this->resolveEvent($segment);
        $profile = $this->getProfile($event);
        $config = $event['config'] ?: ($profile['default_config'] ?? []);
        $calculator = new StandingCalculator();
        $groups = $this->entryModel->getGroupsForEvent((int) $event['id']);

        $data = $this->baseData($event, 'standings');
        $data['standings'] = empty($groups)
            ? ['all' => $calculator->calculate($profile['standing_rule'], (int) $event['id'], null, $config)]
            : $calculator->calculateAllGroups($profile['standing_rule'], (int) $event['id'], $groups, $config);
        $data['topScorers'] = $calculator->topScorers((int) $event['id']);
        return view('sports_event/standings', $data);
    }

    public function stats(string $segment)
    {
        $event = $this->resolveEvent($segment);
        $calculator = new StandingCalculator();
        $data = $this->baseData($event, 'stats');
        $data['topScorers'] = $calculator->topScorers((int) $event['id'], 20);
        $data['teams'] = $this->participantModel->getForEvent((int) $event['id']);
        return view('sports_event/stats', $data);
    }

    public function teams(string $segment)
    {
        $event = $this->resolveEvent($segment);
        $data = $this->baseData($event, 'teams');
        $data['teams'] = $this->participantModel->getForEvent((int) $event['id']);
        return view('sports_event/teams', $data);
    }

    public function team(string $segment, string $teamCode)
    {
        $event = $this->resolveEvent($segment);
        $teams = $this->participantModel->getForEvent((int) $event['id']);
        $team = null;
        foreach ($teams as $t) {
            if (strtolower($t['short_code'] ?? '') === strtolower($teamCode) || generate_slug($t['name_en'] ?: $t['name_bn']) === $teamCode) {
                $team = $t;
                break;
            }
        }
        if (!$team) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $allMatches = $this->matchModel->getForEvent((int) $event['id']);
        $teamMatches = array_filter($allMatches, static fn($m) =>
            (int) $m['participant_a_id'] === (int) $team['id'] || (int) $m['participant_b_id'] === (int) $team['id']
        );

        $data = $this->baseData($event, 'teams');
        $data['team'] = $team;
        $data['matches'] = array_values($teamMatches);
        return view('sports_event/team', $data);
    }

    public function match(string $segment, string $matchSlug)
    {
        $event = $this->resolveEvent($segment);
        $match = $this->matchModel->findByEventAndSlug((int) $event['id'], $matchSlug);
        if (!$match) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->baseData($event, 'match');
        $data['match'] = $match;
        $data['timeline'] = $this->matchEventModel->getForMatch((int) $match['id']);
        $data['title'] = $match['team_a_name'] . ' vs ' . $match['team_b_name'] . ' - ' . $event['title_bn'];

        if (!empty($match['news_id'])) {
            $newsModel = new NewsModel();
            $data['linkedNews'] = $newsModel->find($match['news_id']);
        }
        $data['relatedNews'] = $this->getEventNews($event, 6);

        return view('sports_event/match', $data);
    }

    public function news(string $segment)
    {
        $event = $this->resolveEvent($segment);
        $data = $this->baseData($event, 'news');
        $data['news'] = $this->getEventNews($event, 30);
        return view('sports_event/news', $data);
    }

    protected function getEventNews(array $event, int $limit): array
    {
        $newsModel = new NewsModel();
        if (empty($event['news_tag_slug'])) {
            return $newsModel->where('event_id', $event['id'])->where('status', 'published')
                ->orderBy('published_at', 'DESC')->limit($limit)->findAll();
        }
        return $newsModel->getNewsByTag($event['news_tag_slug'], $limit);
    }
}
