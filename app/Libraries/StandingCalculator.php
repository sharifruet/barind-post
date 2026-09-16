<?php

namespace App\Libraries;

class StandingCalculator
{
    public function calculate(string $rule, int $eventId, ?string $groupName = null, array $config = []): array
    {
        return match ($rule) {
            'football_group'  => $this->footballGroup($eventId, $groupName, $config),
            'cricket_points'  => $this->cricketPoints($eventId, $groupName, $config),
            default           => [],
        };
    }

    public function calculateAllGroups(string $rule, int $eventId, array $groups, array $config = []): array
    {
        $standings = [];
        foreach ($groups as $group) {
            $standings[$group] = $this->calculate($rule, $eventId, $group, $config);
        }
        return $standings;
    }

    protected function footballGroup(int $eventId, ?string $groupName, array $config): array
    {
        $pointsWin  = (int) ($config['points_win'] ?? 3);
        $pointsDraw = (int) ($config['points_draw'] ?? 1);
        $pointsLoss = (int) ($config['points_loss'] ?? 0);

        $matchModel = model('SportsMatchModel');
        $entryModel = model('SportsEventEntryModel');

        $teams = $entryModel->select('sports_participants.id, sports_participants.name_bn, sports_participants.short_code, sports_participants.flag_url, sports_event_entries.group_name')
            ->join('sports_participants', 'sports_participants.id = sports_event_entries.participant_id')
            ->where('sports_event_entries.event_id', $eventId);

        if ($groupName) {
            $teams->where('sports_event_entries.group_name', $groupName);
        }

        $teamRows = $teams->findAll();
        $table = [];

        foreach ($teamRows as $team) {
            $table[$team['id']] = [
                'participant_id' => $team['id'],
                'name_bn'        => $team['name_bn'],
                'short_code'     => $team['short_code'],
                'flag_url'       => $team['flag_url'],
                'group_name'     => $team['group_name'],
                'played'         => 0,
                'won'            => 0,
                'drawn'          => 0,
                'lost'           => 0,
                'gf'             => 0,
                'ga'             => 0,
                'gd'             => 0,
                'points'         => 0,
            ];
        }

        $filters = ['status' => 'finished'];
        if ($groupName) {
            $filters['group_name'] = $groupName;
        }

        $matches = $matchModel->getForEvent($eventId, $filters);

        foreach ($matches as $match) {
            if (!in_array($match['status'], ['ft', 'pen', 'aet'], true)) {
                continue;
            }

            $data = $match['sport_data'];
            $aId = (int) $match['participant_a_id'];
            $bId = (int) $match['participant_b_id'];
            $aScore = (int) ($data['a_score'] ?? 0);
            $bScore = (int) ($data['b_score'] ?? 0);

            if (!isset($table[$aId]) || !isset($table[$bId])) {
                continue;
            }

            $table[$aId]['played']++;
            $table[$bId]['played']++;
            $table[$aId]['gf'] += $aScore;
            $table[$aId]['ga'] += $bScore;
            $table[$bId]['gf'] += $bScore;
            $table[$bId]['ga'] += $aScore;

            if ($aScore > $bScore) {
                $table[$aId]['won']++;
                $table[$aId]['points'] += $pointsWin;
                $table[$bId]['lost']++;
                $table[$bId]['points'] += $pointsLoss;
            } elseif ($aScore < $bScore) {
                $table[$bId]['won']++;
                $table[$bId]['points'] += $pointsWin;
                $table[$aId]['lost']++;
                $table[$aId]['points'] += $pointsLoss;
            } else {
                $table[$aId]['drawn']++;
                $table[$bId]['drawn']++;
                $table[$aId]['points'] += $pointsDraw;
                $table[$bId]['points'] += $pointsDraw;
            }
        }

        foreach ($table as &$row) {
            $row['gd'] = $row['gf'] - $row['ga'];
        }
        unset($row);

        usort($table, static function ($a, $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($a['gd'] !== $b['gd']) {
                return $b['gd'] <=> $a['gd'];
            }
            return $b['gf'] <=> $a['gf'];
        });

        return array_values($table);
    }

    protected function cricketPoints(int $eventId, ?string $groupName, array $config): array
    {
        $pointsWin = (int) ($config['points_win'] ?? 2);
        $pointsNr  = (int) ($config['points_nr'] ?? 1);

        $matchModel = model('SportsMatchModel');
        $entryModel = model('SportsEventEntryModel');

        $teams = $entryModel->select('sports_participants.id, sports_participants.name_bn, sports_participants.short_code, sports_participants.flag_url, sports_event_entries.group_name')
            ->join('sports_participants', 'sports_participants.id = sports_event_entries.participant_id')
            ->where('sports_event_entries.event_id', $eventId);

        if ($groupName) {
            $teams->where('sports_event_entries.group_name', $groupName);
        }

        $teamRows = $teams->findAll();
        $table = [];

        foreach ($teamRows as $team) {
            $table[$team['id']] = [
                'participant_id' => $team['id'],
                'name_bn'        => $team['name_bn'],
                'short_code'     => $team['short_code'],
                'flag_url'       => $team['flag_url'],
                'group_name'     => $team['group_name'],
                'played'         => 0,
                'won'            => 0,
                'lost'           => 0,
                'nr'             => 0,
                'points'         => 0,
                'runs_for'       => 0,
                'runs_against'   => 0,
                'nrr'            => 0.0,
            ];
        }

        $filters = ['status' => 'finished'];
        if ($groupName) {
            $filters['group_name'] = $groupName;
        }

        $matches = $matchModel->getForEvent($eventId, $filters);

        foreach ($matches as $match) {
            if ($match['status'] === 'no_result') {
                $aId = (int) $match['participant_a_id'];
                $bId = (int) $match['participant_b_id'];
                if (isset($table[$aId], $table[$bId])) {
                    $table[$aId]['played']++;
                    $table[$bId]['played']++;
                    $table[$aId]['nr']++;
                    $table[$bId]['nr']++;
                    $table[$aId]['points'] += $pointsNr;
                    $table[$bId]['points'] += $pointsNr;
                }
                continue;
            }

            if ($match['status'] !== 'ft') {
                continue;
            }

            $data = $match['sport_data'];
            $innings = $data['innings'] ?? [];
            if (count($innings) < 2) {
                continue;
            }

            $aId = (int) $match['participant_a_id'];
            $bId = (int) $match['participant_b_id'];
            $aRuns = (int) ($innings[0]['runs'] ?? 0);
            $bRuns = (int) ($innings[1]['runs'] ?? 0);

            if (!isset($table[$aId]) || !isset($table[$bId])) {
                continue;
            }

            $table[$aId]['played']++;
            $table[$bId]['played']++;
            $table[$aId]['runs_for'] += $aRuns;
            $table[$aId]['runs_against'] += $bRuns;
            $table[$bId]['runs_for'] += $bRuns;
            $table[$bId]['runs_against'] += $aRuns;

            if ($aRuns > $bRuns) {
                $table[$aId]['won']++;
                $table[$aId]['points'] += $pointsWin;
                $table[$bId]['lost']++;
            } elseif ($bRuns > $aRuns) {
                $table[$bId]['won']++;
                $table[$bId]['points'] += $pointsWin;
                $table[$aId]['lost']++;
            }
        }

        foreach ($table as &$row) {
            if ($row['played'] > 0) {
                $row['nrr'] = round(($row['runs_for'] - $row['runs_against']) / $row['played'], 3);
            }
        }
        unset($row);

        usort($table, static function ($a, $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            return $b['nrr'] <=> $a['nrr'];
        });

        return array_values($table);
    }

    public function topScorers(int $eventId, int $limit = 10): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('sports_match_events sme')
            ->select('sme.player_name, sme.participant_id, p.name_bn as team_name, p.flag_url, COUNT(*) as goals')
            ->join('sports_matches sm', 'sm.id = sme.match_id')
            ->join('sports_participants p', 'p.id = sme.participant_id', 'left')
            ->where('sm.event_id', $eventId)
            ->whereIn('sme.event_type', ['goal', 'penalty_goal'])
            ->where('sme.player_name IS NOT NULL')
            ->where('sme.player_name !=', '')
            ->groupBy('sme.player_name, sme.participant_id, p.name_bn, p.flag_url')
            ->orderBy('goals', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        return $rows;
    }
}
