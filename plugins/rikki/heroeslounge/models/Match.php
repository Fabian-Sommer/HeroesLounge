<?php namespace Rikki\Heroeslounge\Models;

 
use Illuminate\Support\Facades\DB;
use October\Rain\Database\Model;
use Rikki\Heroeslounge\Models\Timeline;
use October\Rain\Exception\SystemException;
use Rikki\Heroeslounge\classes\hotfixes;
use Rikki\Heroeslounge\classes\Discord\Webhook;
use Rikki\Heroeslounge\Classes\Helpers\TimezoneHelper;
use Rikki\Heroeslounge\Models\Season;
use Rikki\Heroeslounge\Models\Playoff;
use Rikki\Heroeslounge\Models\Team;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Log;
/**
 * Model
 */
class Match extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_match';

    public $hasMany = ['games' => ['Rikki\Heroeslounge\Models\Game']];


    public $belongsTo = [
        'winner' => [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'winner_id'
        ],
        'division' => [
            'Rikki\Heroeslounge\Models\Division',
            'key' => 'div_id',
            'otherKey' => 'id'
        ],
        'playoff' => [
            'Rikki\Heroeslounge\Models\Playoff',
            'key' => 'playoff_id',
            'otherKey' => 'id'
        ]
    ];

    
    public $morphToMany = [
        'timeline' => ['Rikki\Heroeslounge\Models\Timeline',
                    'name' => 'timelineable',
                    'table' => 'rikki_heroeslounge_timelineables']
    ];

    public $belongsToMany = [
        'teams' => [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'match_id',
            'otherKey' => 'team_id',
            'table' => 'rikki_heroeslounge_team_match',
            'pivot' => ['team_score','team_id']
        ],
        'casters' => [
            'Rikki\Heroeslounge\Models\Sloth',
            'key' => 'match_id',
            'otherKey' => 'caster_id',
            'table' => 'rikki_heroeslounge_match_caster',
            'pivot' => ['approved']
        ],
        'channels' => [
            'Rikki\Heroeslounge\Models\Twitchchannel',
            'key' => 'match_id',
            'otherKey' => 'channel_id',
            'table' => 'rikki_heroeslounge_match_channel'
        ]
    ];

    public function listParticipatingTeamsForBackend($fieldname, $value, $formData)
    {
        $match = Match::findOrFail($formData["id"]);
        $list = [];
        foreach ($match->teams as $key => $team) {
            $list[$team->id] = $team->title;
        }
        return $list;
    }

    public function getRegionAttribute()
    {
        $match = Match::with('teams')->find($this->id);

        if (isset($match->teams[0])) {
            return $match->teams[0]->region->title;
        }
        return null;
    }

    public function getLoserAttribute()
    {
        if (!$this->winner) {
            return null;
        }
        if ($this->teams->count() != 2) {
            return null;
        }
        $winner_id = $this->winner_id;
        return $this->teams->first(
            function ($team, $key) use ($winner_id) {
                return $team->id != $winner_id;
            });
    }

    public function getChannelAttribute()
    {
        return $this->channels->count() > 0 ? $this->channels->first() : null;
    }

    public function getSeasonAttribute()
    {
        if ($this->division != null && $this->division->season != null) {
            return $this->division->season;
        } elseif ($this->playoff != null && $this->playoff->season != null) {
            return $this->playoff->season;
        } elseif ($this->division != null && $this->division->playoff != null && $this->division->playoff->season != null) {
            return $this->division->playoff->season;
        }
        return null;
    }

    public function getCasterIds()
    {
        return $this->casters
        ->map(function ($item, $key) {
            return $item->id;
        });
    }

    public function getAppliedCasterTitles()
    {
        return $this->casters
        ->filter(function ($item) {
            return $item->pivot->approved == 0;
        })
        ->map(function ($item, $key) {
            return $item->title;
        });
    }

    public function getAcceptedCasters()
    {
        return $this->casters
        ->filter(function ($item) {
            return $item->pivot->approved == 1;
        });
    }

    public function getAppliedCasterIds()
    {
        return $this->casters
        ->filter(function ($item) {
            return $item->pivot->approved == 0;
        })
        ->map(function ($item, $key) {
            return $item->id;
        });
    }

    public function belongsToSeason($season)
    {
        return $this->season && $this->season->id == $season->id;
    }

    public function beforeUpdate()
    {
        if ($this->isDirty('winner_id')) {
            if (isset($this->getOriginal()['winner_id']) && !is_null($this->division)) {
                DB::table('rikki_heroeslounge_team_division')
                                    ->where('team_id', $this->getOriginal()['winner_id'])
                                    ->where('div_id', $this->div_id)
                                    ->where('win_count', '>', 0)
                                    ->decrement('win_count', 1);
            }
            if (isset($this->getOriginal()['winner_id']) && !is_null($this->playoff)) {
                //remove former winner from next match
                if (!is_null($this->playoff_winner_next)) {
                    $winnerTeam = Team::find($this->getOriginal()['winner_id']);
                    $nextWinnerMatch = $this->playoff->matches()->where('playoff_position', $this->playoff_winner_next)->first();
                    $nextWinnerMatch->teams()->remove($winnerTeam);
                }
                //remove former loser from next match
                if (!is_null($this->playoff_loser_next)) {
                    $loserTeam = $this->teams()->where('rikki_heroeslounge_teams.id', '!=', $this->getOriginal()['winner_id'])->first();
                    $nextLoserMatch = $this->playoff->matches()->where('playoff_position', $this->playoff_loser_next)->first();
                    $nextLoserMatch->teams()->remove($loserTeam);
                }
            }
        }
    }

    public function afterUpdate()
    {
        if (!empty($this->original['wbp']) && !empty($this->wbp) && $this->wbp != $this->original['wbp']) {
            $appliedCasters = $this->casters->filter(function ($item) {
                return $item->pivot->approved == 0 || $item->pivot->approved == 1;
            });

            if ($appliedCasters->count() > 0) {
                $newDate = new DateTime($this->wbp, new DateTimeZone(TimezoneHelper::defaultTimezone()));
                $oldDate = new DateTime($this->original['wbp'], new DateTimeZone(TimezoneHelper::defaultTimezone()));

                $regionDefaultTimezone;
                if ($this->teams[0]->region_id == 1) {
                    $regionDefaultTimezone = new DateTimeZone('Europe/Berlin');
                } else if ($this->teams[0]->region_id == 2) {
                    $regionDefaultTimezone = new DateTimeZone('America/Los_Angeles');
                }

                $newDate->setTimezone($regionDefaultTimezone);
                $oldDate->setTimezone($regionDefaultTimezone);

                // Create our information message to inform assigned / pending casters.
                $embed = [
                    "title" => "Match Reschedule",
                    "description" => "",
                    "url" => "",
                    "fields" => [
                        [
                            "name" => "Previous time",
                            "value" => "",
                            "inline" => true
                        ],
                        [
                            "name" => "New time",
                            "value" => "",
                            "inline" => true
                        ]
                    ]
                ];

                $embed["url"] .= "https://heroeslounge.gg/match/view/" . $this->id;
                if ($this->teams->count() >= 2) {
                    $embed["description"] .= $this->teams[0]->title . " VS " . $this->teams[1]->title;
                } else {
                    $embed["description"] .= "Teams currently unknown";
                }

                $embed["fields"][0]["value"] .= $oldDate->format('d M Y H:i T');
                $embed["fields"][1]["value"] .= $newDate->format('d M Y H:i T');

                $message = "";
                foreach ($appliedCasters as $caster) {
                    if (!empty($caster->discord_id)) {
                        $message .= "<@" . $caster->discord_id . ">\n";
                    } else {
                        $message .= $caster->title . "\n";
                    }
                }
                
                Webhook::sendMatchReschedule($message, $embed);
            }
        }
    }

    public function afterSave()
    {
        if ($this->isDirty('winner_id')) {
            //division
            if (!is_null($this->winner) && !is_null($this->division)) {
                DB::table('rikki_heroeslounge_team_division')
                                    ->where('team_id', $this->winner->id)
                                    ->where('div_id', $this->div_id)
                                    ->increment('win_count', 1);
            }
            
            //timeline
            $scheduledTimelineEntry = $this->timeline()->where('type', 'Match.Played')->first();
            if (is_null($scheduledTimelineEntry)) {
                $timeline = new Timeline();
                $timeline->type = 'Match.Played';
                $timeline->save();
                $timeline->matches()->add($this);
                if (!is_null($this->division)) {
                    $this->teams->each(function ($team) {
                    DB::table('rikki_heroeslounge_team_division')
                                        ->where('team_id', $team->id)
                                        ->where('div_id', $this->div_id)
                                        ->increment('match_count', 1);
                    });
                }
                

                $this->is_played = true;

                if ($this->wbp == null) {
                    $this->wbp = date('Y-m-d H:i:s');
                }

                $this->save();
            } else {
                $scheduledTimelineEntry->created_at = date('Y-m-d H:i:s');
                $scheduledTimelineEntry->save();
            }
            //playoff
            if (!is_null($this->winner) && !is_null($this->playoff_id)) {
                //add winner to next match
                if (!is_null($this->playoff_winner_next)) {
                    $nextWinnerMatch = $this->playoff->matches()->where('playoff_position', $this->playoff_winner_next)->first();
                    if ($nextWinnerMatch->teams()->where('title', $this->winner->title)->count() == 0) {
                        $nextWinnerMatch->teams()->add($this->winner);
                    } else {
                        Log::info('Match '.$this->id.' afterSave twice');
                        Log::info(json_encode(debug_backtrace()));
                    }
                }
                
                //add loser to next match
                if (!is_null($this->playoff_loser_next)) {
                    $loserTeam = $this->teams()->where('rikki_heroeslounge_teams.id', '!=', $this->winner_id)->first();
                    $nextLoserMatch = $this->playoff->matches()->where('playoff_position', $this->playoff_loser_next)->first();
                    if ($nextLoserMatch->teams()->where('title', $loserTeam->title)->count() == 0) {
                        $nextLoserMatch->teams()->add($loserTeam);
                    } else {
                        Log::info('Match '.$this->id.' afterSave twice');
                        Log::info(json_encode(debug_backtrace()));
                    }
                }
            }
        }

        //REMOVE ASAP WHEN TIME TO BUGFIX
        if (!is_null($this->division)) {
            if (!is_null($this->division->season_id)) {
                $season = $this->division->season;
                $hotfix = new hotfixes\DivisionTableFix;
                $hotfix->fixTables($season);
            }
        }
    }

    public function afterDelete()
    {
        $this->teams()->detach();
        $this->casters()->detach();
        
        foreach ($this->games as $game) {
            $game->delete();
        }
    }

    public function determineWinnerAndSave()
    {
        $t1wins = $this->games->where('winner_id', $this->teams[0]->id)->count();
        $t2wins = $this->games->where('winner_id', $this->teams[1]->id)->count();
        if ($t1wins < $t2wins) {
            $this->winner_id = $this->teams[1]->id;
        } elseif ($t1wins > $t2wins) {
            $this->winner_id = $this->teams[0]->id;
        } else {
            $this->winner_id = null;
        }
        $this->is_played = true;
        $this->save();
    }

    public function allowRescheduleByCaptain()
    {
        $season = $this->getSeasonAttribute();
        if ($this->playoff != null || ($season != null && $season->type  == 2)) {
            return false;
        }

        return true;
    }

    //encode triple with Cantor
    public static function encodePlayoffPosition($bracket, $round, $matchnumber) 
    {
        $temp1 = (($bracket + $round) * ($bracket + $round + 1) / 2) + $round;
        return (($matchnumber + $temp1) * ($matchnumber + $temp1 + 1) / 2) + $temp1;
    }

    //decode triple with Cantor
    public static function decodePlayoffPosition($pp) 
    {
        $w1 = floor((sqrt(8*$pp + 1) - 1) / 2);
        $t1 = ($w1 * $w1 + $w1) / 2;
        $y1 = $pp - $t1;
        $matchnumber = $w1 - $y1;

        $w2 = floor((sqrt(8*$y1 + 1) - 1) / 2);
        $t2 = ($w2 * $w2 + $w2) / 2;
        $round = $y1 - $t2;
        $bracket = $w2 - $round;
        return ['bracket' => $bracket, 'round' => $round, 'matchnumber' => $matchnumber];
    }
}
