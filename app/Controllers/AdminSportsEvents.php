<?php

namespace App\Controllers;

use App\Models\SportsEventModel;
use App\Models\SportsParticipantModel;
use App\Models\SportsVenueModel;
use App\Models\SportsMatchModel;
use App\Models\SportsMatchEventModel;
use App\Models\SportsEventEntryModel;
use App\Models\NewsModel;
use App\Models\TagModel;
use App\Libraries\StandingCalculator;

class AdminSportsEvents extends BaseAdminController
{
    protected $allowedRoles = ['admin', 'editor', 'sub-editor'];

    protected SportsEventModel $eventModel;
    protected SportsParticipantModel $participantModel;
    protected SportsVenueModel $venueModel;
    protected SportsMatchModel $matchModel;
    protected SportsMatchEventModel $matchEventModel;
    protected SportsEventEntryModel $entryModel;

    public function __construct()
    {
        $this->eventModel = new SportsEventModel();
        $this->participantModel = new SportsParticipantModel();
        $this->venueModel = new SportsVenueModel();
        $this->matchModel = new SportsMatchModel();
        $this->matchEventModel = new SportsMatchEventModel();
        $this->entryModel = new SportsEventEntryModel();
        helper(['sports_event', 'slug']);
    }

    protected function getEventOr404(int $id): array
    {
        $event = $this->eventModel->find($id);
        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return $this->eventModel->decodeConfig($event);
    }

    protected function getProfile(array $event): array
    {
        $profiles = config('SportProfiles');
        return $profiles->get($event['sport_profile']) ?? $profiles->get('football');
    }

    // ─── Events list & CRUD ───────────────────────────────────────────

    public function index()
    {
        $events = $this->eventModel->orderBy('start_date', 'DESC')->findAll();
        return view('admin/sports_events/index', [
            'title'  => 'Sports Events',
            'events' => $events,
        ]);
    }

    public function create()
    {
        $profiles = config('SportProfiles');
        return view('admin/sports_events/form', [
            'title'    => 'Create Sports Event',
            'event'    => null,
            'profiles' => $profiles->listForSelect(),
        ]);
    }

    public function store()
    {
        $data = $this->collectEventData();
        if (!$this->eventModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->eventModel->errors()));
        }
        $eventId = $this->eventModel->getInsertID();
        $this->ensureNewsTag($data['news_tag_slug'] ?? '');
        return redirect()->to('/admin/sports-events/manage/' . $eventId)->with('success', 'Event created successfully.');
    }

    public function edit(int $id)
    {
        $event = $this->getEventOr404($id);
        $profiles = config('SportProfiles');
        return view('admin/sports_events/form', [
            'title'    => 'Edit Sports Event',
            'event'    => $event,
            'profiles' => $profiles->listForSelect(),
        ]);
    }

    public function update(int $id)
    {
        $this->getEventOr404($id);
        $data = $this->collectEventData();
        if (!$this->eventModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', 'Failed to update event.');
        }
        $this->ensureNewsTag($data['news_tag_slug'] ?? '');
        return redirect()->to('/admin/sports-events/manage/' . $id)->with('success', 'Event updated successfully.');
    }

    public function delete(int $id)
    {
        $this->getEventOr404($id);
        $this->eventModel->delete($id);
        return redirect()->to('/admin/sports-events')->with('success', 'Event deleted.');
    }

    protected function collectEventData(): array
    {
        $slug = $this->request->getPost('slug') ?: generate_slug($this->request->getPost('title_en') ?: $this->request->getPost('title_bn'));
        $customUrl = trim($this->request->getPost('custom_url') ?? '');
        $newsTag = trim($this->request->getPost('news_tag_slug') ?? '') ?: $slug;

        $config = [
            'points_win'  => (int) $this->request->getPost('points_win'),
            'points_draw' => (int) $this->request->getPost('points_draw'),
            'points_loss' => (int) $this->request->getPost('points_loss'),
            'points_nr'   => (int) $this->request->getPost('points_nr'),
            'overs'       => (int) $this->request->getPost('overs'),
        ];

        return [
            'slug'                 => $slug,
            'custom_url'           => $customUrl ?: null,
            'title_bn'             => $this->request->getPost('title_bn'),
            'title_en'             => $this->request->getPost('title_en'),
            'sport_profile'        => $this->request->getPost('sport_profile'),
            'description_bn'       => $this->request->getPost('description_bn'),
            'banner_image'         => $this->request->getPost('banner_image') ?: null,
            'logo_image'           => $this->request->getPost('logo_image') ?: null,
            'news_tag_slug'        => $newsTag,
            'start_date'           => $this->request->getPost('start_date') ?: null,
            'end_date'             => $this->request->getPost('end_date') ?: null,
            'status'               => $this->request->getPost('status') ?: 'draft',
            'show_in_nav'          => $this->request->getPost('show_in_nav') ? 1 : 0,
            'show_homepage_widget' => $this->request->getPost('show_homepage_widget') ? 1 : 0,
            'config'               => json_encode(array_filter($config, static fn($v) => $v !== 0 && $v !== null)),
        ];
    }

    protected function ensureNewsTag(string $tagSlug): void
    {
        if (empty($tagSlug)) {
            return;
        }
        $tagModel = new TagModel();
        if (!$tagModel->where('name', $tagSlug)->first()) {
            $tagModel->insert(['name' => $tagSlug]);
        }
    }

    // ─── Event dashboard ──────────────────────────────────────────────

    public function manage(int $id)
    {
        $event = $this->getEventOr404($id);
        $profile = $this->getProfile($event);
        $todayMatches = $this->matchModel->getTodayMatches($id);
        $liveMatches = $this->matchModel->getLiveMatches($id);
        $upcoming = $this->matchModel->getForEvent($id, ['status' => 'upcoming']);
        $teamCount = $this->entryModel->where('event_id', $id)->countAllResults();
        $matchCount = $this->matchModel->where('event_id', $id)->countAllResults();

        return view('admin/sports_events/manage', [
            'title'        => 'Manage: ' . $event['title_bn'],
            'event'        => $event,
            'profile'      => $profile,
            'todayMatches' => $todayMatches,
            'liveMatches'  => $liveMatches,
            'upcoming'     => array_slice($upcoming, 0, 5),
            'teamCount'    => $teamCount,
            'matchCount'   => $matchCount,
        ]);
    }

    // ─── Participants library ─────────────────────────────────────────

    public function participants()
    {
        $participants = $this->participantModel->orderBy('name_bn', 'ASC')->findAll();
        return view('admin/sports_events/participants', [
            'title'        => 'Teams / Participants',
            'participants' => $participants,
        ]);
    }

    public function addParticipant()
    {
        $data = [
            'name_bn'    => $this->request->getPost('name_bn'),
            'name_en'    => $this->request->getPost('name_en'),
            'short_code' => strtoupper($this->request->getPost('short_code') ?? ''),
            'flag_url'   => $this->request->getPost('flag_url') ?: null,
            'type'       => $this->request->getPost('type') ?: 'team',
        ];
        $this->participantModel->insert($data);
        $redirect = $this->request->getPost('redirect') ?: '/admin/sports-events/participants';
        return redirect()->to($redirect)->with('success', 'Participant added.');
    }

    public function editParticipant(int $id)
    {
        $participant = $this->participantModel->find($id);
        if (!$participant) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return view('admin/sports_events/participant_form', [
            'title'       => 'Edit Participant',
            'participant' => $participant,
        ]);
    }

    public function updateParticipant(int $id)
    {
        $data = [
            'name_bn'    => $this->request->getPost('name_bn'),
            'name_en'    => $this->request->getPost('name_en'),
            'short_code' => strtoupper($this->request->getPost('short_code') ?? ''),
            'flag_url'   => $this->request->getPost('flag_url') ?: null,
            'type'       => $this->request->getPost('type') ?: 'team',
        ];
        $this->participantModel->update($id, $data);
        return redirect()->to('/admin/sports-events/participants')->with('success', 'Participant updated.');
    }

    public function deleteParticipant(int $id)
    {
        $this->participantModel->delete($id);
        return redirect()->back()->with('success', 'Participant deleted.');
    }

    // ─── Event teams ──────────────────────────────────────────────────

    public function eventTeams(int $eventId)
    {
        $event = $this->getEventOr404($eventId);
        $teams = $this->participantModel->getForEvent($eventId);
        $available = $this->participantModel->getNotInEvent($eventId);
        $groups = $this->entryModel->getGroupsForEvent($eventId);

        return view('admin/sports_events/event_teams', [
            'title'     => 'Teams - ' . $event['title_bn'],
            'event'     => $event,
            'teams'     => $teams,
            'available' => $available,
            'groups'    => $groups,
        ]);
    }

    public function addEventTeam(int $eventId)
    {
        $this->getEventOr404($eventId);
        $this->entryModel->insert([
            'event_id'       => $eventId,
            'participant_id' => $this->request->getPost('participant_id'),
            'group_name'     => $this->request->getPost('group_name') ?: null,
            'seed'           => $this->request->getPost('seed') ?: null,
        ]);
        return redirect()->to('/admin/sports-events/' . $eventId . '/teams')->with('success', 'Team added to event.');
    }

    public function updateEventTeam(int $eventId, int $entryId)
    {
        $this->getEventOr404($eventId);
        $this->entryModel->update($entryId, [
            'group_name' => $this->request->getPost('group_name') ?: null,
            'seed'       => $this->request->getPost('seed') ?: null,
        ]);
        return redirect()->to('/admin/sports-events/' . $eventId . '/teams')->with('success', 'Team updated.');
    }

    public function removeEventTeam(int $eventId, int $entryId)
    {
        $this->getEventOr404($eventId);
        $this->entryModel->delete($entryId);
        return redirect()->to('/admin/sports-events/' . $eventId . '/teams')->with('success', 'Team removed from event.');
    }

    // ─── Venues ───────────────────────────────────────────────────────

    public function venues()
    {
        $venues = $this->venueModel->orderBy('name_bn', 'ASC')->findAll();
        return view('admin/sports_events/venues', [
            'title'  => 'Stadiums / Venues',
            'venues' => $venues,
        ]);
    }

    public function addVenue()
    {
        $this->venueModel->insert([
            'name_bn'   => $this->request->getPost('name_bn'),
            'name_en'   => $this->request->getPost('name_en'),
            'city'      => $this->request->getPost('city'),
            'country'   => $this->request->getPost('country'),
            'capacity'  => $this->request->getPost('capacity') ?: null,
            'image_url' => $this->request->getPost('image_url') ?: null,
        ]);
        $redirect = $this->request->getPost('redirect') ?: '/admin/sports-events/venues';
        return redirect()->to($redirect)->with('success', 'Venue added.');
    }

    public function editVenue(int $id)
    {
        $venue = $this->venueModel->find($id);
        if (!$venue) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return view('admin/sports_events/venue_form', [
            'title' => 'Edit Venue',
            'venue' => $venue,
        ]);
    }

    public function updateVenue(int $id)
    {
        $this->venueModel->update($id, [
            'name_bn'   => $this->request->getPost('name_bn'),
            'name_en'   => $this->request->getPost('name_en'),
            'city'      => $this->request->getPost('city'),
            'country'   => $this->request->getPost('country'),
            'capacity'  => $this->request->getPost('capacity') ?: null,
            'image_url' => $this->request->getPost('image_url') ?: null,
        ]);
        return redirect()->to('/admin/sports-events/venues')->with('success', 'Venue updated.');
    }

    public function deleteVenue(int $id)
    {
        $this->venueModel->delete($id);
        return redirect()->back()->with('success', 'Venue deleted.');
    }

    // ─── Matches / fixtures ───────────────────────────────────────────

    public function matches(int $eventId)
    {
        $event = $this->getEventOr404($eventId);
        $profile = $this->getProfile($event);
        $filterStatus = $this->request->getGet('status');
        $filterGroup = $this->request->getGet('group');
        $filters = array_filter(['status' => $filterStatus, 'group_name' => $filterGroup]);
        $matches = $this->matchModel->getForEvent($eventId, $filters);
        $groups = $this->entryModel->getGroupsForEvent($eventId);

        return view('admin/sports_events/matches', [
            'title'        => 'Fixtures - ' . $event['title_bn'],
            'event'        => $event,
            'profile'      => $profile,
            'matches'      => $matches,
            'groups'       => $groups,
            'filterStatus' => $filterStatus,
            'filterGroup'  => $filterGroup,
        ]);
    }

    public function createMatch(int $eventId)
    {
        $event = $this->getEventOr404($eventId);
        $profile = $this->getProfile($event);
        $teams = $this->participantModel->getForEvent($eventId);
        $venues = $this->venueModel->orderBy('name_bn', 'ASC')->findAll();
        $groups = $this->entryModel->getGroupsForEvent($eventId);

        return view('admin/sports_events/match_form', [
            'title'         => 'Add Fixture',
            'event'         => $event,
            'profile'       => $profile,
            'match'         => null,
            'teams'         => $teams,
            'venues'        => $venues,
            'groups'        => $groups,
            'timeline'      => [],
            'newsArticles'  => $this->getEventNews($event),
        ]);
    }

    public function storeMatch(int $eventId)
    {
        $event = $this->getEventOr404($eventId);
        $matchId = $this->saveMatchData($event, null);
        return redirect()->to('/admin/sports-events/' . $eventId . '/matches/edit/' . $matchId)->with('success', 'Match created.');
    }

    public function editMatch(int $eventId, int $matchId)
    {
        $event = $this->getEventOr404($eventId);
        $profile = $this->getProfile($event);
        $match = $this->matchModel->getWithDetails($matchId);
        if (!$match || (int) $match['event_id'] !== $eventId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $teams = $this->participantModel->getForEvent($eventId);
        $venues = $this->venueModel->orderBy('name_bn', 'ASC')->findAll();
        $groups = $this->entryModel->getGroupsForEvent($eventId);
        $timeline = $this->matchEventModel->getForMatch($matchId);

        return view('admin/sports_events/match_form', [
            'title'        => 'Edit Match',
            'event'        => $event,
            'profile'      => $profile,
            'match'        => $match,
            'teams'        => $teams,
            'venues'       => $venues,
            'groups'       => $groups,
            'timeline'     => $timeline,
            'newsArticles' => $this->getEventNews($event),
        ]);
    }

    public function updateMatch(int $eventId, int $matchId)
    {
        $event = $this->getEventOr404($eventId);
        $match = $this->matchModel->find($matchId);
        if (!$match || (int) $match['event_id'] !== $eventId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->saveMatchData($event, $matchId);
        return redirect()->to('/admin/sports-events/' . $eventId . '/matches/edit/' . $matchId)->with('success', 'Match updated.');
    }

    public function deleteMatch(int $eventId, int $matchId)
    {
        $this->getEventOr404($eventId);
        $this->matchEventModel->deleteForMatch($matchId);
        $this->matchModel->delete($matchId);
        return redirect()->to('/admin/sports-events/' . $eventId . '/matches')->with('success', 'Match deleted.');
    }

    protected function saveMatchData(array $event, ?int $matchId): int
    {
        $profile = $this->getProfile($event);
        $sportData = $this->collectSportData($event['sport_profile'], $profile);

        $teamA = $this->request->getPost('participant_a_id');
        $teamB = $this->request->getPost('participant_b_id');
        $slug = $this->request->getPost('match_slug') ?: $this->generateMatchSlug($teamA, $teamB);

        $data = [
            'event_id'         => $event['id'],
            'participant_a_id' => $teamA,
            'participant_b_id' => $teamB,
            'venue_id'         => $this->request->getPost('venue_id') ?: null,
            'kickoff_at'       => $this->request->getPost('kickoff_at') ?: null,
            'stage'            => $this->request->getPost('stage') ?: 'group',
            'group_name'       => $this->request->getPost('group_name') ?: null,
            'match_slug'       => $slug,
            'status'           => $this->request->getPost('status') ?: 'scheduled',
            'summary_bn'       => $this->request->getPost('summary_bn'),
            'news_id'          => $this->request->getPost('news_id') ?: null,
            'sport_data'       => json_encode($sportData),
            'referee'          => $this->request->getPost('referee') ?: null,
            'attendance'       => $this->request->getPost('attendance') ?: null,
        ];

        if ($matchId) {
            $this->matchModel->update($matchId, $data);
        } else {
            $this->matchModel->insert($data);
            $matchId = (int) $this->matchModel->getInsertID();
        }

        $this->saveTimelineEvents($matchId);

        return $matchId;
    }

    protected function collectSportData(string $sportProfile, array $profile): array
    {
        if (str_starts_with($sportProfile, 'cricket')) {
            $format = str_contains($sportProfile, 'odi') ? 'odi' : 't20';
            $overs = (int) ($this->request->getPost('overs_limit') ?: ($profile['default_config']['overs'] ?? 20));
            return [
                'format'      => $format,
                'result_text' => $this->request->getPost('result_text') ?? '',
                'overs_limit' => $overs,
                'innings'     => [
                    [
                        'team_side' => 'a',
                        'runs'      => $this->nullableInt($this->request->getPost('inn_a_runs')),
                        'wickets'   => $this->nullableInt($this->request->getPost('inn_a_wickets')),
                        'overs'     => $this->request->getPost('inn_a_overs') ?: null,
                    ],
                    [
                        'team_side' => 'b',
                        'runs'      => $this->nullableInt($this->request->getPost('inn_b_runs')),
                        'wickets'   => $this->nullableInt($this->request->getPost('inn_b_wickets')),
                        'overs'     => $this->request->getPost('inn_b_overs') ?: null,
                    ],
                ],
            ];
        }

        return [
            'a_score' => $this->nullableInt($this->request->getPost('a_score')),
            'b_score' => $this->nullableInt($this->request->getPost('b_score')),
            'a_ht'    => $this->nullableInt($this->request->getPost('a_ht')),
            'b_ht'    => $this->nullableInt($this->request->getPost('b_ht')),
            'a_pen'   => $this->nullableInt($this->request->getPost('a_pen')),
            'b_pen'   => $this->nullableInt($this->request->getPost('b_pen')),
        ];
    }

    protected function saveTimelineEvents(int $matchId): void
    {
        $this->matchEventModel->deleteForMatch($matchId);

        $minutes = $this->request->getPost('tl_minute') ?? [];
        $types = $this->request->getPost('tl_type') ?? [];
        $teams = $this->request->getPost('tl_team') ?? [];
        $players = $this->request->getPost('tl_player') ?? [];
        $details = $this->request->getPost('tl_detail') ?? [];

        foreach ($minutes as $i => $minute) {
            if (empty($types[$i])) {
                continue;
            }
            $this->matchEventModel->insert([
                'match_id'       => $matchId,
                'event_minute'   => $minute,
                'event_type'     => $types[$i],
                'participant_id' => $teams[$i] ?: null,
                'player_name'    => $players[$i] ?? null,
                'detail'         => $details[$i] ?? null,
                'sort_order'     => $i,
            ]);
        }
    }

    protected function nullableInt($value): ?int
    {
        return ($value === '' || $value === null) ? null : (int) $value;
    }

    protected function generateMatchSlug($teamAId, $teamBId): string
    {
        $a = $this->participantModel->find($teamAId);
        $b = $this->participantModel->find($teamBId);
        $parts = [];
        if ($a) {
            $parts[] = strtolower($a['short_code'] ?: generate_slug($a['name_en'] ?: $a['name_bn']));
        }
        if ($b) {
            $parts[] = strtolower($b['short_code'] ?: generate_slug($b['name_en'] ?: $b['name_bn']));
        }
        return implode('-vs-', $parts) . '-' . time();
    }

    protected function getEventNews(array $event): array
    {
        $newsModel = new NewsModel();
        if (empty($event['news_tag_slug'])) {
            return $newsModel->where('status', 'published')->orderBy('published_at', 'DESC')->limit(50)->findAll();
        }
        return $newsModel->getNewsByTag($event['news_tag_slug'], 50);
    }

    // ─── Standings & stats preview ──────────────────────────────────────

    public function standings(int $eventId)
    {
        $event = $this->getEventOr404($eventId);
        $profile = $this->getProfile($event);
        $config = $event['config'] ?? [];
        if (empty($config)) {
            $config = $profile['default_config'] ?? [];
        }
        $calculator = new StandingCalculator();
        $groups = $this->entryModel->getGroupsForEvent($eventId);
        $standings = empty($groups)
            ? ['all' => $calculator->calculate($profile['standing_rule'], $eventId, null, $config)]
            : $calculator->calculateAllGroups($profile['standing_rule'], $eventId, $groups, $config);
        $topScorers = $calculator->topScorers($eventId);

        return view('admin/sports_events/standings', [
            'title'      => 'Standings - ' . $event['title_bn'],
            'event'      => $event,
            'profile'    => $profile,
            'standings'  => $standings,
            'topScorers' => $topScorers,
        ]);
    }

    public function eventNews(int $eventId)
    {
        $event = $this->getEventOr404($eventId);
        $news = $this->getEventNews($event);
        return view('admin/sports_events/event_news', [
            'title' => 'News - ' . $event['title_bn'],
            'event' => $event,
            'news'  => $news,
        ]);
    }
}
