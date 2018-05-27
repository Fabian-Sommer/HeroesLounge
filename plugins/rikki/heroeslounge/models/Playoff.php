<?php namespace Rikki\Heroeslounge\Models;

use Model;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\Models\Match;
use Carbon\Carbon;
use Log;
class Playoff extends Model
{
    public $table = 'rikki_heroeslounge_playoffs';

    public $belongsTo = [
        'season' => [
            'Rikki\Heroeslounge\Models\Season',
            'key' => 'season_id',
            'otherKey' => 'id'
		]
    ];

    public $hasMany = [
    	'divisions' => ['Rikki\Heroeslounge\Models\Division'],
        'matches' => ['Rikki\Heroeslounge\Models\Match']
    ];

    public $belongsToMany = [
        'teams' =>
        [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'playoff_id',
            'otherKey' => 'team_id',
            'table' => 'rikki_heroeslounge_team_playoff',
            'pivot' => ['seed']
        ],
    ];

    public function getLongTitleAttribute() 
    {
        if ($this->season != null) {
            return $this->season->title . ' - ' . $this->title;
        }
        return $this->title;
    }

    public function createMatches()
    {
        $playoff = $this;
        if ($this->type == 'playoffv1') {
            $a1 = $this->teams()->where('seed', 1)->firstOrFail();
            $a2 = $this->teams()->where('seed', 5)->firstOrFail();
            $a3 = $this->teams()->where('seed', 9)->firstOrFail();
            $a4 = $this->teams()->where('seed', 13)->firstOrFail();

            $b1 = $this->teams()->where('seed', 2)->firstOrFail();
            $b2 = $this->teams()->where('seed', 6)->firstOrFail();
            $b3 = $this->teams()->where('seed', 10)->firstOrFail();
            $b4 = $this->teams()->where('seed', 14)->firstOrFail();

            $c1 = $this->teams()->where('seed', 3)->firstOrFail();
            $c2 = $this->teams()->where('seed', 7)->firstOrFail();
            $c3 = $this->teams()->where('seed', 11)->firstOrFail();
            $c4 = $this->teams()->where('seed', 15)->firstOrFail();

            $d1 = $this->teams()->where('seed', 4)->firstOrFail();
            $d2 = $this->teams()->where('seed', 8)->firstOrFail();
            $d3 = $this->teams()->where('seed', 12)->firstOrFail();
            $d4 = $this->teams()->where('seed', 16)->firstOrFail();

            
            $groups = [
                0 => ['title' => 'Group A', 'slug' => 'group-a',
                        'teams' => [0 =>$a1,1 => $a2,2 => $a3,3 => $a4]],
                1 => ['title' => 'Group B', 'slug' => 'group-b',
                        'teams' => [0 =>$b1,1 => $b2,2 => $b3,3 => $b4]],
                2 => ['title' => 'Group C', 'slug' => 'group-c',
                        'teams' => [0 =>$c1,1 => $c2,2 => $c3,3 => $c4]],
                3 => ['title' => 'Group D', 'slug' => 'group-d',
                        'teams' => [0 =>$d1,1 => $d2,2 => $d3,3 => $d4]]
            ];
            $groups_until = Carbon::create(2018, 2, 16, 23, 59, 59, 'Europe/Berlin');
            foreach ($groups as $key => $groupe) {
                $gr = new Division;
                $gr->playoff = $playoff;
                $gr->title = $groupe['title'];
                $gr->slug = $groupe['slug'];
                $gr->save();
                $gr->teams()->add($groupe['teams'][0]);
                $gr->teams()->add($groupe['teams'][1]);
                $gr->teams()->add($groupe['teams'][2]);
                $gr->teams()->add($groupe['teams'][3]);
                $match = new Match;
                $match->division = $gr;
                $match->tbp = $groups_until;
                $match->schedule_date = $groups_until;
                $match->round = 0;
                $match->save();
                $match->teams()->add($groupe['teams'][0]);
                $match->teams()->add($groupe['teams'][1]);
                $match = new Match;
                $match->division = $gr;
                $match->tbp = $groups_until;
                $match->schedule_date = $groups_until;
                $match->round = 0;
                $match->save();
                $match->teams()->add($groupe['teams'][2]);
                $match->teams()->add($groupe['teams'][3]);
                $match = new Match;
                $match->division = $gr;
                $match->tbp = $groups_until;
                $match->schedule_date = $groups_until;
                $match->round = 0;
                $match->save();
                $match->teams()->add($groupe['teams'][0]);
                $match->teams()->add($groupe['teams'][2]);
                $match = new Match;
                $match->division = $gr;
                $match->tbp = $groups_until;
                $match->schedule_date = $groups_until;
                $match->round = 0;
                $match->save();
                $match->teams()->add($groupe['teams'][1]);
                $match->teams()->add($groupe['teams'][3]);
                $match = new Match;
                $match->division = $gr;
                $match->tbp = $groups_until;
                $match->schedule_date = $groups_until;
                $match->round = 0;
                $match->save();
                $match->teams()->add($groupe['teams'][0]);
                $match->teams()->add($groupe['teams'][3]);
                $match = new Match;
                $match->division = $gr;
                $match->tbp = $groups_until;
                $match->schedule_date = $groups_until;
                $match->round = 0;
                $match->save();   
                $match->teams()->add($groupe['teams'][1]);
                $match->teams()->add($groupe['teams'][2]);     
            }
            //TODO configure dates
            $matchArray = [
                0 => [  'pos' => Match::encodePlayoffPosition(1,1,1), 
                        'wn' => Match::encodePlayoffPosition(1,2,1), 
                        'ln' => Match::encodePlayoffPosition(2,3,2), 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 18, 15, 0, 'Europe/Berlin')],
                1 => [  'pos' => Match::encodePlayoffPosition(1,1,2), 
                        'wn' => Match::encodePlayoffPosition(1,2,1), 
                        'ln' => Match::encodePlayoffPosition(2,3,1), 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 18, 15, 0, 'Europe/Berlin')],
                2 => [  'pos' => Match::encodePlayoffPosition(2,1,1), 
                        'wn' => Match::encodePlayoffPosition(2,2,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 13, 00, 0, 'Europe/Berlin')],
                3 => [  'pos' => Match::encodePlayoffPosition(2,1,2), 
                        'wn' => Match::encodePlayoffPosition(2,2,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 13, 00, 0, 'Europe/Berlin')],
                4 => [  'pos' => Match::encodePlayoffPosition(2,1,3), 
                        'wn' => Match::encodePlayoffPosition(2,2,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 14, 45, 0, 'Europe/Berlin')],
                5 => [  'pos' => Match::encodePlayoffPosition(2,1,4), 
                        'wn' => Match::encodePlayoffPosition(2,2,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 14, 45, 0, 'Europe/Berlin')],
                6 => [  'pos' => Match::encodePlayoffPosition(2,2,1), 
                        'wn' => Match::encodePlayoffPosition(2,3,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 16, 30, 0, 'Europe/Berlin')],
                7 => [  'pos' => Match::encodePlayoffPosition(2,2,2), 
                        'wn' => Match::encodePlayoffPosition(2,3,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 16, 30, 0, 'Europe/Berlin')],
                8 => [  'pos' => Match::encodePlayoffPosition(2,3,1), 
                        'wn' => Match::encodePlayoffPosition(2,4,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 20, 00, 0, 'Europe/Berlin')],
                9 => [  'pos' => Match::encodePlayoffPosition(2,3,2), 
                        'wn' => Match::encodePlayoffPosition(2,4,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 17, 20, 00, 0, 'Europe/Berlin')],
                10 => [ 'pos' => Match::encodePlayoffPosition(2,4,1), 
                        'wn' => Match::encodePlayoffPosition(2,5,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 18, 14, 45, 0, 'Europe/Berlin')],
                11 => [ 'pos' => Match::encodePlayoffPosition(1,2,1), 
                        'wn' => Match::encodePlayoffPosition(3,1,1), 
                        'ln' => Match::encodePlayoffPosition(2,5,1), 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 18, 13, 00, 0, 'Europe/Berlin')],
                12 => [ 'pos' => Match::encodePlayoffPosition(3,1,1), 
                        'wn' => null, 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 18, 20, 00, 0, 'Europe/Berlin')],
                13 => [ 'pos' => Match::encodePlayoffPosition(2,5,1), 
                        'wn' => Match::encodePlayoffPosition(3,1,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => Carbon::create(2018, 2, 18, 18, 15, 0, 'Europe/Berlin')]
            ];
        } else if ($this->type == 'se16') {
            $firstRoundTime = Carbon::create(2018, 2, 17, 18, 15, 0, 'Europe/Berlin');
            $secondRoundTime = Carbon::create(2018, 2, 17, 18, 15, 0, 'Europe/Berlin');
            $thirdRoundTime = Carbon::create(2018, 2, 17, 18, 15, 0, 'Europe/Berlin');
            $fourthRoundTime = Carbon::create(2018, 2, 17, 18, 15, 0, 'Europe/Berlin');
            $matchArray = [
                0 => [  'pos' => Match::encodePlayoffPosition(1,1,1), 
                        'wn' => Match::encodePlayoffPosition(1,2,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $firstRoundTime],
                1 => [  'pos' => Match::encodePlayoffPosition(1,1,2), 
                        'wn' => Match::encodePlayoffPosition(1,2,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $firstRoundTime],
                2 => [  'pos' => Match::encodePlayoffPosition(1,1,3), 
                        'wn' => Match::encodePlayoffPosition(1,2,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $firstRoundTime],
                3 => [  'pos' => Match::encodePlayoffPosition(1,1,4), 
                        'wn' => Match::encodePlayoffPosition(1,2,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $firstRoundTime],
                4 => [  'pos' => Match::encodePlayoffPosition(1,1,5), 
                        'wn' => Match::encodePlayoffPosition(1,2,3), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $firstRoundTime],
                5 => [  'pos' => Match::encodePlayoffPosition(1,1,6), 
                        'wn' => Match::encodePlayoffPosition(1,2,3), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $firstRoundTime],
                6 => [  'pos' => Match::encodePlayoffPosition(1,1,7), 
                        'wn' => Match::encodePlayoffPosition(1,2,4), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $firstRoundTime],
                7 => [  'pos' => Match::encodePlayoffPosition(1,1,8), 
                        'wn' => Match::encodePlayoffPosition(1,2,4), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $firstRoundTime],
                8 => [  'pos' => Match::encodePlayoffPosition(1,2,1), 
                        'wn' => Match::encodePlayoffPosition(1,3,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $secondRoundTime],
                9 => [  'pos' => Match::encodePlayoffPosition(1,2,2), 
                        'wn' => Match::encodePlayoffPosition(1,3,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $secondRoundTime],
                10 => [ 'pos' => Match::encodePlayoffPosition(1,2,3), 
                        'wn' => Match::encodePlayoffPosition(1,3,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $secondRoundTime],
                11 => [ 'pos' => Match::encodePlayoffPosition(1,2,4), 
                        'wn' => Match::encodePlayoffPosition(1,3,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $secondRoundTime],
                12 => [ 'pos' => Match::encodePlayoffPosition(1,3,1), 
                        'wn' => Match::encodePlayoffPosition(1,4,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $thirdRoundTime],
                13 => [ 'pos' => Match::encodePlayoffPosition(1,3,2), 
                        'wn' => Match::encodePlayoffPosition(1,4,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $thirdRoundTime],
                14 => [ 'pos' => Match::encodePlayoffPosition(1,4,1), 
                        'wn' => null, 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $fourthRoundTime],
            ];
        }

        foreach ($matchArray as $key => $matchEntry) {
            $match = new Match;
            $match->playoff_id = $playoff->id;
            $match->wbp = $matchEntry['wbp'];
            $match->playoff_position = $matchEntry['pos'];
            $match->playoff_winner_next = $matchEntry['wn'];
            $match->playoff_loser_next = $matchEntry['ln'];
            $match->save();
            foreach ($matchEntry['teams'] as $key => $team) {
                $match->teams()->add($team);
            }
        }
        $this->teams()->detach();
    }

    //assign seeded teams to knockout stage matches
    public function seedTeams()
    {
        if ($this->type == 'playoffv1') {
            $tems = [];
            for ($i=1; $i <= 12; $i++) { 
                $team = $this->teams()->where('seed', $i)->first();
                if ($team) {
                    $tems[$i] = $team;
                } else {
                    Log::info('Adding BYE '. $i);
                    $tems[$i] = Team::where('title', 'BYE!')->firstOrFail();
                }
            }
            $mat = [];
            $mat[1] = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(1,1,1))->firstOrFail();
            $mat[2] = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(1,1,2))->firstOrFail();
            $mat[3] = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(2,1,1))->firstOrFail();
            $mat[4] = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(2,1,2))->firstOrFail();
            $mat[5] = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(2,1,3))->firstOrFail();
            $mat[6] = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(2,1,4))->firstOrFail();
            for ($i=1; $i <= 6; $i++) { 
                $match = $mat[$i];
                $team1 = $tems[2*$i-1];
                $team2 = $tems[2*$i];
                $match->teams()->detach();
                $match->teams()->add($team1);
                $match->teams()->add($team2);
                if ($team2->title == 'BYE!') {
                    $match->winner_id = $team1->id;
                    $match->save();
                }
            }
        } else if ($this->type == 'se16') {
            $tems = [];
            for ($i=1; $i <= 16; $i++) { 
                $team = $this->teams()->where('seed', $i)->first();
                if ($team) {
                    $tems[$i] = $team;
                } else {
                    Log::info('Adding BYE '. $i);
                    $tems[$i] = Team::where('title', 'BYE!')->firstOrFail();
                }
            }
            for ($i=1; $i <= 8; $i++) { 
                $match = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(1,1,$i))->firstOrFail();
                $team1 = $tems[$i];
                $team2 = $tems[17-$i];
                $match->teams()->detach();
                $match->teams()->add($team1);
                $match->teams()->add($team2);
                if ($team2->title == 'BYE!') {
                    $match->winner_id = $team1->id;
                    $match->save();
                }
            }
        }
    }

    public function getSeedsFromGroups()
    {

        if ($this->type == 'playoffv1') {
            $this->teams()->detach();
            $ga = $this->divisions()->where('title', 'Group A')->firstOrFail();
            $gb = $this->divisions()->where('title', 'Group B')->firstOrFail();
            $gc = $this->divisions()->where('title', 'Group C')->firstOrFail();
            $gd = $this->divisions()->where('title', 'Group D')->firstOrFail();

            $teams = $ga->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 1));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 5));
            $this->teams()->attach(array_values($teams)[2]['id'], array('seed' => 12));

            $teams = $gb->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 3));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 9));
            $this->teams()->attach(array_values($teams)[2]['id'], array('seed' => 8));

            $teams = $gc->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 4));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 11));
            $this->teams()->attach(array_values($teams)[2]['id'], array('seed' => 6));

            $teams = $gd->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 2));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 7));
            $this->teams()->attach(array_values($teams)[2]['id'], array('seed' => 10));
        } else if ($this->type == 'se16') {
            //nothing to do here
        }
    }
}
