<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\classes\helpers\MatchContextResolver;
use Rikki\Heroeslounge\Models\MatchModel;
use Rikki\Heroeslounge\Models\Team;
use Rikki\LoungeStatistics\classes\statistics\Statistics;

class TeamStatistics extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Team Statistics',
            'description' => 'Provides HeroesLounge Team Statistics',
        ];
    }

    public $team = null;
    public $heroes = null;
    public $maps = null;
    public $participatedSeasons = null;
    public $selectedSeasons = [];
    public $lastThree = null;
    public $loaded = false;

    /**
     * id, div_id, playoff_id of all matches of the team, with a resolver for their seasons.
     */
    protected $matchIndex = null;
    protected $resolver = null;

    public function init()
    {
        // Runs for every AJAX request on the page, so no data is loaded here.
        $this->addJs('assets/js/datatables.min.js');
        $this->addJs('assets/js/loungestatistics.js');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/lazy-content.js');
        $this->addCss('assets/css/datatables.min.css');
    }

    public function onRender()
    {
        if (!$this->property('lazy')) {
            $this->loadDefaultStatistics();
        }
    }

    /**
     * Loads the season selection and the statistics of the default season.
     */
    public function onLoadStatistics()
    {
        $this->loadDefaultStatistics();
        return [
            '#' . $this->alias . '_container' => $this->renderPartial('@content'),
        ];
    }

    public function onSeasonChange()
    {
        $this->loadTeam();
        $season_id = input('season_id');
        if ($season_id === 'all-time') {
            $seasons = $this->participatedSeasons;
        } else if ($season_id === 'all-time-real-seasons') {
            $seasons = $this->participatedSeasons->filter(function ($season) {
                return in_array($season->type, [1, 2]);
            });
        } else if ($season_id === 'last-three') {
            $seasons = $this->participatedSeasons->whereIn('id', $this->lastThree->keys());
        } else {
            $seasons = $this->participatedSeasons->where('id', $season_id);
        }
        $this->selectedSeasons = $seasons->pluck('id')->toArray();
        $this->calculateStats($this->selectedSeasons);
        return [
            '#teamstatistics' => $this->renderPartial('@stats'),
        ];
    }

    protected function loadDefaultStatistics()
    {
        $this->loadTeam();
        $activeSeasons = $this->participatedSeasons->filter(function ($season) {
            return $season->is_active;
        });
        $selectedSeason = $activeSeasons->count() > 0 ? $activeSeasons->take(1) : $this->participatedSeasons->take(1);
        $this->selectedSeasons = $selectedSeason->pluck('id')->toArray();
        $this->calculateStats($this->selectedSeasons);
        $this->loaded = true;
    }

    /**
     * Loads the team and the seasons it played in, without loading its matches.
     */
    protected function loadTeam()
    {
        $this->team = Team::findOrFail($this->property('team_id'));

        $this->matchIndex = MatchContextResolver::teamMatches($this->team->id)
            ->select('id', 'div_id', 'playoff_id')
            ->toBase()
            ->get();
        $this->resolver = new MatchContextResolver($this->matchIndex);

        $this->participatedSeasons = $this->matchIndex
            ->map(function ($match) {
                return $this->resolver->season($match->div_id, $match->playoff_id);
            })
            ->filter()
            ->keyBy('id')
            ->sortByDesc('created_at')
            ->values();

        $this->lastThree = $this->participatedSeasons->filter(
            function ($season) {
                return in_array($season->type, [1, 2]);
            }
        )->take(3)->pluck('title', 'id');
    }

    /**
     * Calculates the statistics from only the matches played in the given seasons.
     */
    public function calculateStats($seasons)
    {
        $matchIds = $this->matchIndex
            ->filter(function ($match) use ($seasons) {
                $season = $this->resolver->season($match->div_id, $match->playoff_id);
                return $season && in_array($season->id, $seasons);
            })
            ->pluck('id');

        $matches = $matchIds->isEmpty() ? collect() : MatchModel::whereIn('id', $matchIds)
            ->with('games', 'games.replay', 'games.map', 'games.gameParticipations', 'games.gameParticipations.hero', 'games.teamOneFirstBan', 'games.teamOneSecondBan', 'games.teamTwoFirstBan', 'games.teamTwoSecondBan', 'games.teamOneThirdBan', 'games.teamTwoThirdBan')
            ->get();
        $this->team->setRelation('matches', $matches);

        // The matches are already limited to the selected seasons.
        $heroes = Statistics::calculateHeroStatisticsForTeam($this->team, null);
        $this->heroes = $heroes->sortByDesc(
            function ($hero_array) {
                return $hero_array['picks'] + $hero_array['bans_by_team'] + $hero_array['bans_against_team'];
            }
        );

        $maps = Statistics::calculateMapStatisticsForTeam($this->team, null);
        $this->maps = $maps->sortByDesc(
            function ($map_array) {
                return $map_array['picks_by'] + $map_array['picks_vs'];
            }
        );
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
                'validationMessage' => 'The team_id property can contain only numeric symbols',
            ],
            'lazy' => [
                'title' => 'Lazy',
                'description' => 'Only load the statistics once they become visible',
                'default' => false,
                'type' => 'checkbox',
            ],
        ];
    }
}
