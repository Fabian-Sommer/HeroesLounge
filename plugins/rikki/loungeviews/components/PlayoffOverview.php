<?php namespace Rikki\LoungeViews\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\Models\Season;
use Rikki\Heroeslounge\Models\Playoff;
use Rikki\Heroeslounge\Classes\Helpers\TimezoneHelper;
use Redirect;
use Flash;
use Log;
use Auth;

class PlayoffOverview extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'PlayoffOverview',
            'description' => 'Displays a Playoff'
        ];
    }
    public $season = null;
    public $userCaptainedTeams = null;
    public $playoff = null;
    public $matches = null;
    public $user = null;
    public $timezone = null;
    public $timezoneOffset = null;
    public $timeFormat = null;
    public $userTeamSignedUp = false;
    public $polylines = null;
    public $match_height = 3.875;
    public $match_width = 13;
    public $width_between_matches = 2;
    public $total_width = 105;
    public $total_height = 43.9375;

    public function init()
    {
        $this->user = Auth::getUser();
        if ($this->param('season-slug')) {
            $this->season = Season::where('slug',$this->param('season-slug'))->first();
            if ($this->season) {
                $this->playoff = $this->season->playoffs()->where('title',$this->param('playoff-title'))->first();
            }
        } else {
            $this->playoff = Playoff::where('slug',$this->param('playoff-title'))->first();
        }
        
        if ($this->playoff) {
            $this->page->title = $this->playoff->longTitle;
            if ($this->user) {
                $this->userCaptainedTeams = $this->user->sloth->getCaptainedTeams();
                foreach ($this->user->sloth->teams as $key => $team) {
                    if ($this->playoff->teams->contains($team)) {
                        $this->userTeamSignedUp = true;
                    }
                }
            }

            if ($this->playoff->type == 'playoffv1') {
                $this->total_height = 43.9375;
                $this->total_width = 105;
            } else if ($this->playoff->type == 'se16') {
                $this->total_height = 43.9375;
                $this->total_width = 60;
            } else if ($this->playoff->type == 'se8' || $this->playoff->type == 'playoffv2') {
                $this->total_height = 43.9375;
                $this->total_width = 45;
            } else if ($this->playoff->type == 'se32') {
                $this->total_height = 78;
                $this->total_width = 75;
            } else if ($this->playoff->type == 'se64') {
                $this->total_height = 153;
                $this->total_width = 90;
            } else if ($this->playoff->type == 'de16') {
                $this->total_height = 60;
                $this->total_width = 106;
            } else if ($this->playoff->type == 'playoffv3') {
                $this->total_height = 43.9375;
                $this->total_width = 60;
            } else if ($this->playoff->type == 'DivSv1') {
                $this->total_height = 25;
                $this->total_width = 75;
            }
            $this->matches = [];
            $this->polylines = [];
            foreach ($this->playoff->matches as $match) {
                $array = [];
                $array['model'] = $match;
                $offset_left = 30;
                $offset_top = 2.6875;
                //for now, special case only
                $decoded_playoff_position = Match::decodePlayoffPosition($match->playoff_position);
                $array['position_bracket_name'] = $this->getBracketName($decoded_playoff_position);
                $offsets = $this->getOffsetsForMatch($decoded_playoff_position);
                $array['offset_left'] = $offsets['left'];
                $array['offset_top'] = $offsets['top'];
                if ($match->playoff_winner_next) {
                    $decoded_playoff_winner_next = Match::decodePlayoffPosition($match->playoff_winner_next);
                    $this->addWinnerLinkBetweenMatches($decoded_playoff_position, $decoded_playoff_winner_next);
                }
                if ($match->playoff_loser_next) {
                    $decoded_playoff_loser_next = Match::decodePlayoffPosition($match->playoff_loser_next);
                    $this->addLoserLinkToMatch($decoded_playoff_loser_next);
                }
                $this->matches[] = $array;
            }
        }

        $this->addComponent(
            'Rikki\Heroeslounge\Components\RoundMatches',
            'roundMatches',
            [
                'deferredBinding'   => true,
                'showLogo' => true,
                'showName' => true,
                'type' => 'division'
            ]
        );
        $this->addComponent(
            'Rikki\LoungeViews\Components\DivisionTable',
            'divisionTable',
            [
                'deferredBinding'   => true,
                'showScore' => true
            ]
        );
       
    }

    public function onRun()
    {
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/ResizeSensor.js');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/ElementQueries.js');
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/heroeslounge.css');
    }

    public function defineProperties()
    {
        return [
        ];
    }

    public function onRender()
    {
        $this->timezone = TimezoneHelper::getTimezone();
        $this->timezoneOffset = TimezoneHelper::getTimezoneOffset();
        $this->timeFormat = TimezoneHelper::getTimeFormatString();
    }

    private function getBracketName($dec_position)
    {
        if ($dec_position['bracket'] == 1) return 'winners';
        if ($dec_position['bracket'] == 2) return 'losers';
        if ($dec_position['bracket'] == 3) return 'finals';
        return 'badbracket';
    }

    //gets offsets in rem for a decoded playoff_position
    public function getOffsetsForMatch($dec_position)
    {
        $left = 0;
        $top = 0;
        $round_width = $this->match_width + $this->width_between_matches;
        if ($this->playoff->type == 'playoffv1') {
            if ($dec_position['bracket'] == 1) {
                //winners bracket
                $left = $round_width + (2 * $round_width * ($dec_position['round'] - 1));
                if ($dec_position['round'] == 1) {
                    if ($dec_position['matchnumber'] == 1) {
                        $top = 2.6875;
                    } else if ($dec_position['matchnumber'] == 2) {
                        $top = 11.9375;
                    }
                } else if ($dec_position['round'] == 2) {
                    $top = 7.3125;
                }
            } else if ($dec_position['bracket'] == 2) {
                //losers bracket
                $left = $round_width * ($dec_position['round'] - 1);
                switch($dec_position['round']) {
                    case 1:
                        switch ($dec_position['matchnumber']) {
                            case 1:
                                $top = 23.5;
                                break;
                            case 2:
                                $top = 28.125;
                                break;
                            case 3:
                                $top = 35.0625;
                                break;
                            case 4:
                                $top = 39.6875;
                                break;
                        }
                        break;
                    case 2:
                        switch ($dec_position['matchnumber']) {
                            case 1:
                                $top = 25.8125;
                                break;
                            case 2:
                                $top = 37.375;
                                break;
                        }
                        break;
                    case 3:
                        switch ($dec_position['matchnumber']) {
                            case 1:
                                $top = 21.1875;
                                break;
                            case 2:
                                $top = 32.75;
                                break;
                        }
                        break;
                    case 4:
                        $top = 26.96875;
                        break;
                    case 5:
                        $top = 18.875;
                        break;
                }
            } else if ($dec_position['bracket'] == 3) {
                //finals
                $left = 5 * $round_width;
                $top = 13.09375;
            }
        } else if ($this->playoff->type == 'se16' || $this->playoff->type == 'se8' || $this->playoff->type == 'se32' || $this->playoff->type == 'se64' || $this->playoff->type == 'playoffv2') {
            //winners bracket only
            $left = $round_width * ($dec_position['round']-1);
            $diff = 4.625;
            $top = 2.6875 + (2**($dec_position['round']-1) - 1) * $diff / 2 + ($dec_position['matchnumber']-1) * $diff* 2**($dec_position['round']-1);
        } else if ($this->playoff->type == 'playoffv3') {
            if ($dec_position['bracket'] == 1) {
                //winners bracket
                $left = $round_width + (2 * $round_width * ($dec_position['round'] - 1));
                $top = 2.6875;
            } else if ($dec_position['bracket'] == 2) {
                //losers bracket
                $left = $round_width * ($dec_position['round'] - 1);
                switch($dec_position['round']) {
                    case 1:
                        switch ($dec_position['matchnumber']) {
                            case 1:
                                $top = 7.5;
                                break;
                            case 2:
                                $top = 12.125;
                                break;
                        }
                        break;
                    case 2:
                        $top = 9.8125;
                        break;
                    case 3:
                        $top = 6.8125;
                        break;
                }
            } else if ($dec_position['bracket'] == 3) {
                //finals
                $left = 3 * $round_width;
                $top = 5;
            }
        } else if ($this->playoff->type == 'de16') {
            if ($dec_position['bracket'] == 1) {
                $left = 2 * $round_width * ($dec_position['round']-1);
                $diff = 4.625;
                $top = 2.6875 + (2**($dec_position['round']-1) - 1) * $diff / 2 + ($dec_position['matchnumber']-1) * $diff* 2**($dec_position['round']-1);
            } else if ($dec_position['bracket'] == 2) {
                $left = $round_width * $dec_position['round'];
                $diff = 4.625;
                $top = 40.0625 + (2**(floor(($dec_position['round']-1)/2)) - 1) * $diff / 2 + ($dec_position['matchnumber']-1) * $diff* 2**(floor(($dec_position['round']-1)/2));
            } else if ($dec_position['bracket'] == 3) {
                $left = $round_width*7;
                $top = 33.0625;
            }
        } else if ($this->playoff->type == 'DivSv1') {
            if ($dec_position['bracket'] == 1) {
                $left = 2 * $round_width * ($dec_position['round']-1);
                if ($dec_position['matchnumber'] == 1) {
                    $top = 2.6875;
                } else if ($dec_position['matchnumber'] == 2) {
                    $top = 2.6875 + 4.625;
                }
            } else if ($dec_position['bracket'] == 2) {
                $left = $round_width * $dec_position['round'];
                if ($dec_position['matchnumber'] == 1) {
                    $top = 2.6875 + 10;
                } else {
                    $top = 2.6875 + 4.625 + 10;
                }
            } else if ($dec_position['bracket'] == 3) {
                if ($dec_position['matchnumber'] == 1) {
                    $left = $round_width*4;
                    $top = 5;
                } else {
                    $left = $round_width*3;
                    $top = 15;
                }
            }
        }
        
        return ['left' => $left, 'top' => $top];
    }

    public function addWinnerLinkBetweenMatches($dec_position1, $dec_position2)
    {
        $offsets1 = $this->getOffsetsForMatch($dec_position1);
        $offsets2 = $this->getOffsetsForMatch($dec_position2);
        $fourth_x = $offsets2['left'] * 1000;
        $fourth_y = ($offsets2['top'] + (0.5 * $this->match_height)) * 1000;
        $first_x = ($offsets1['left'] + $this->match_width) * 1000;
        $first_y = ($offsets1['top'] + (0.5 * $this->match_height)) * 1000;
        $middle_x = ($offsets2['left'] - (0.5 * $this->width_between_matches)) * 1000;

        $line = [];
        $line[] = $first_x.','.$first_y;
        $line[] = $middle_x.','.$first_y;
        $line[] = $middle_x.','.$fourth_y;
        $line[] = $fourth_x.','.$fourth_y;
        $this->polylines[] = $line;
    }

    public function addLoserLinkToMatch($dec_position)
    {
        $offsets = $this->getOffsetsForMatch($dec_position);
        //1 rem is 1000
        $third_x = $offsets['left'] * 1000;
        $third_y = ($offsets['top'] + (0.5 * $this->match_height)) * 1000;
        $first_x = ($offsets['left'] - (0.5 * $this->width_between_matches)) * 1000;
        $first_y = ($offsets['top'] + (0.25 * $this->match_height)) * 1000;

        $line = [];
        $line[] = $first_x.','.$first_y;
        $line[] = $first_x.','.$third_y;
        $line[] = $third_x.','.$third_y;
        $this->polylines[] = $line;
    }

    public function onTeamSignup()
    {
        $this->user = Auth::getUser();
        $this->playoff = Playoff::find($_POST['playoff_id']);
        if ($this->user != null) {
            $team = $this->user->sloth->teams->where('id', $_POST['team_id'])->first();
            if ($team != null && $team->pivot->is_captain && $team->region_id == $this->playoff->region_id && !$this->playoff->teams->contains($team)) {
                $eligible = $team->isEligibleForPlayoff($this->playoff);
                if ($eligible === true && $team->sloths->count() >= 5) {
                    $this->playoff->teams()->add($team);
                    Flash::success('Your team is now signed up for '.$this->playoff->title);
                    return Redirect::refresh();
                } elseif ($team->sloths->count() < 5) {
                    Flash::error('Your team must contain at least 5 players!');
                    return Redirect::refresh();
                } else {
                    //$eligible holds a sloth that is already participating with another team
                    Flash::error('A member of this team is already participating with another team: '.$eligible->title);
                    return Redirect::refresh();
                }
                
            }
        }
    }
}
