<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\LoungeStatistics\classes\statistics\Statistics;
use Rikki\Heroeslounge\Models\Hero;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\Season;
use Rikki\Heroeslounge\Models\GameParticipation;

use October\Rain\Support\Collection;
use Db;
use Log;

class TeamStatistics extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Team Statistics',
            'description' => 'Provides HeroesLounge Team Statistics'
        ];
    }

    public $team = null;
    public $heroes = null;
    public $maps = null;
    public $participatedSeasons = null;
    public $selectedSeason = null;

    public function init()
    {
        $this->addJs('assets/js/datatables.min.js');
        $this->addJs('assets/js/loungestatistics.js');
        $this->addCss('assets/css/datatables.min.css');

        $this->team = Team::where('id', $this->property('team_id'))
            ->with('matches', 'matches.games', 'matches.games.map', 'matches.games.gameParticipations', 'matches.games.gameParticipations.hero', 'matches.games.teamOneFirstBan', 'matches.games.teamOneSecondBan', 'matches.games.teamTwoFirstBan', 'matches.games.teamTwoSecondBan', 'matches.games.teamOneThirdBan', 'matches.games.teamTwoThirdBan')
            ->first();

        $this->participatedSeasons = $this->team->matches
            ->map(function ($match) { return $match->season; })
            ->filter(function ($season) { return $season != null; })
            ->groupBy('id')->map(function ($group) { return $group[0]; })  // unique does not work on these
            ->sortByDesc('created_at')->values();

        $this->selectedSeason = $this->participatedSeasons
            ->filter(function ($season) { return $season->is_active; })
            ->last() ?? $this->participatedSeasons->first();

        $this->calculateStats($this->selectedSeason);
    }

    public function calculateStats($season)
    {
        $heroes = Statistics::calculateHeroStatisticsForTeam($this->team, $season);
        $this->heroes = $heroes->sortByDesc(function ($hero_array) {
            return $hero_array['picks'] + $hero_array['bans_by_team'] + $hero_array['bans_against_team'];
        });

        $maps = Statistics::calculateMapStatisticsForTeam($this->team, $season);
        $this->maps = $maps->sortByDesc(function ($map_array) {
            return $map_array['picks_by'] + $map_array['picks_vs'];
        });
    }

    public function onSeasonChange()
    {
        $season_id = input('season_id');
        if ($season_id == -1) {
            $this->selectedSeason = null;
        } else {
            $this->selectedSeason = Season::find($season_id);
        }
        $this->calculateStats($this->selectedSeason);
        return [
            '#teamstatistics' => $this->renderPartial('@stats')
        ];
    }

    public function defineProperties()
    {
       return [
            'team_id' => [
                'title' => 'Team ID',
                'description' => 'Team ID to grab data from',
                'default' => 0,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The team_id property can contain only numeric symbols'
            ]
        ];
    }
}
