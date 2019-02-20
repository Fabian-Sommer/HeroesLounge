<?php namespace Rikki\Heroeslounge\Models;

use Model;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\Models\Match;
use Carbon\Carbon;
use Log;
class Playoff extends Model
{
    public $table = 'rikki_heroeslounge_playoffs';

    protected $slugs = ['slug' => 'title'];

    public $belongsTo = [
        'season' => [
            'Rikki\Heroeslounge\Models\Season',
            'key' => 'season_id',
            'otherKey' => 'id'
		],
        'region' => ['Rikki\Heroeslounge\Models\Region']
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
        $timezone = 'Europe/Berlin';
        if ($this->type == 'playoffv1') {
            //TODO configure dates and timezone from backend
            $timezone = 'Europe/Berlin';
            $year0 = 2018;
            $month0 = 10;
            $day0 = 12;
            $year1 = 2018;
            $month1 = 10;
            $day1 = 13;
            $year2 = 2018;
            $month2 = 10;
            $day2 = 14;

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
            $groups_until = Carbon::create($year0, $month0, $day0, 23, 59, 59, $timezone);
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
            
            $Time1 = Carbon::create($year1, $month1, $day1, 13, 00, 0, $timezone);
            $Time2 = Carbon::create($year1, $month1, $day1, 14, 45, 0, $timezone);
            $Time3 = Carbon::create($year1, $month1, $day1, 16, 30, 0, $timezone);
            $Time4 = Carbon::create($year1, $month1, $day1, 18, 15, 0, $timezone);
            $Time5 = Carbon::create($year1, $month1, $day1, 20, 00, 0, $timezone);
            $Time6 = Carbon::create($year2, $month2, $day2, 13, 00, 0, $timezone);
            $Time7 = Carbon::create($year2, $month2, $day2, 14, 45, 0, $timezone);
            $Time8 = Carbon::create($year2, $month2, $day2, 18, 15, 0, $timezone);
            $Time9 = Carbon::create($year2, $month2, $day2, 20, 30, 0, $timezone);
            
            $matchArray = [
                0 => [  'pos' => Match::encodePlayoffPosition(1,1,1), 
                        'wn' => Match::encodePlayoffPosition(1,2,1), 
                        'ln' => Match::encodePlayoffPosition(2,3,2), 
                        'teams' => [], 
                        'wbp' => $Time3],
                1 => [  'pos' => Match::encodePlayoffPosition(1,1,2), 
                        'wn' => Match::encodePlayoffPosition(1,2,1), 
                        'ln' => Match::encodePlayoffPosition(2,3,1), 
                        'teams' => [], 
                        'wbp' => $Time3],
                2 => [  'pos' => Match::encodePlayoffPosition(2,1,1), 
                        'wn' => Match::encodePlayoffPosition(2,2,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time1],
                3 => [  'pos' => Match::encodePlayoffPosition(2,1,2), 
                        'wn' => Match::encodePlayoffPosition(2,2,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time1],
                4 => [  'pos' => Match::encodePlayoffPosition(2,1,3), 
                        'wn' => Match::encodePlayoffPosition(2,2,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time2],
                5 => [  'pos' => Match::encodePlayoffPosition(2,1,4), 
                        'wn' => Match::encodePlayoffPosition(2,2,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time2],
                6 => [  'pos' => Match::encodePlayoffPosition(2,2,1), 
                        'wn' => Match::encodePlayoffPosition(2,3,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time4],
                7 => [  'pos' => Match::encodePlayoffPosition(2,2,2), 
                        'wn' => Match::encodePlayoffPosition(2,3,2), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time4],
                8 => [  'pos' => Match::encodePlayoffPosition(2,3,1), 
                        'wn' => Match::encodePlayoffPosition(2,4,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time5],
                9 => [  'pos' => Match::encodePlayoffPosition(2,3,2), 
                        'wn' => Match::encodePlayoffPosition(2,4,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time5],
                10 => [ 'pos' => Match::encodePlayoffPosition(2,4,1), 
                        'wn' => Match::encodePlayoffPosition(2,5,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time7],
                11 => [ 'pos' => Match::encodePlayoffPosition(1,2,1), 
                        'wn' => Match::encodePlayoffPosition(3,1,1), 
                        'ln' => Match::encodePlayoffPosition(2,5,1), 
                        'teams' => [], 
                        'wbp' => $Time6],
                12 => [ 'pos' => Match::encodePlayoffPosition(3,1,1), 
                        'wn' => null, 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time9],
                13 => [ 'pos' => Match::encodePlayoffPosition(2,5,1), 
                        'wn' => Match::encodePlayoffPosition(3,1,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time8]
            ];
        } else if ($this->type == 'playoffv2') {
            $timezone = 'Europe/Berlin';
            $year = 2019;
            $month = 3;
            $day = 2;

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
            $groups_until = Carbon::create($year, $month, $day, 23, 59, 59, $timezone)->subDays(2);
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

            $times = [  0 => Carbon::create($year, $month, $day, 13, 00, 0, $timezone),
                        1 => Carbon::create($year, $month, $day, 16, 00, 0, $timezone),
                        2 => Carbon::create($year, $month, $day, 17, 30, 0, $timezone)
                    ];
            $otherTime = Carbon::create($year, $month, $day, 14, 30, 0, $timezone);
            $matchArray = $this->createSEMatches(3, $times);
            $matchArray[2]['wbp'] = $otherTime;
            $matchArray[3]['wbp'] = $otherTime;
        } else if ($this->type == 'playoffv3') {
            $timezone = 'Europe/Berlin';
            $year = 2019;
            $month = 3;
            $day = 3;

            $a1 = $this->teams()->where('seed', 1)->firstOrFail();
            $a2 = $this->teams()->where('seed', 3)->firstOrFail();
            $a3 = $this->teams()->where('seed', 5)->firstOrFail();
            $a4 = $this->teams()->where('seed', 7)->firstOrFail();

            $b1 = $this->teams()->where('seed', 2)->firstOrFail();
            $b2 = $this->teams()->where('seed', 4)->firstOrFail();
            $b3 = $this->teams()->where('seed', 6)->firstOrFail();
            $b4 = $this->teams()->where('seed', 8)->firstOrFail();

            $groups = [
                0 => ['title' => 'Group A', 'slug' => 'group-a',
                        'teams' => [0 =>$a1,1 => $a2,2 => $a3,3 => $a4]],
                1 => ['title' => 'Group B', 'slug' => 'group-b',
                        'teams' => [0 =>$b1,1 => $b2,2 => $b3,3 => $b4]]
            ];
            $groups_until = Carbon::create($year, $month, $day, 23, 59, 59, $timezone)->subDays(3);
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

            $Time1 = Carbon::create($year, $month, $day, 13, 00, 0, $timezone);
            $Time2 = Carbon::create($year, $month, $day, 14, 45, 0, $timezone);
            $Time3 = Carbon::create($year, $month, $day, 18, 15, 0, $timezone);
            $Time4 = Carbon::create($year, $month, $day, 20, 00, 0, $timezone);
            
            $matchArray = [
                0 => [  'pos' => Match::encodePlayoffPosition(1,1,1), 
                        'wn' => Match::encodePlayoffPosition(3,1,1), 
                        'ln' => Match::encodePlayoffPosition(2,3,1), 
                        'teams' => [], 
                        'wbp' => $Time2],
                1 => [  'pos' => Match::encodePlayoffPosition(2,1,1), 
                        'wn' => Match::encodePlayoffPosition(2,2,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time1],
                2 => [  'pos' => Match::encodePlayoffPosition(2,1,2), 
                        'wn' => Match::encodePlayoffPosition(2,2,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time1],
                3 => [  'pos' => Match::encodePlayoffPosition(2,2,1), 
                        'wn' => Match::encodePlayoffPosition(2,3,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time2],
                4 => [  'pos' => Match::encodePlayoffPosition(2,3,1), 
                        'wn' => Match::encodePlayoffPosition(3,1,1), 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time3],
                5 => [ 'pos' => Match::encodePlayoffPosition(3,1,1), 
                        'wn' => null, 
                        'ln' => null, 
                        'teams' => [], 
                        'wbp' => $Time4]
            ];
        } else if ($this->type == 'se16') {
            $times = [  0 => Carbon::create(2019, 2, 17, 1, 0, 0, $timezone),
                        1 => Carbon::create(2019, 2, 17, 2, 0, 0, $timezone),
                        2 => Carbon::create(2019, 2, 17, 1, 0, 0, $timezone),
                        3 => Carbon::create(2019, 2, 17, 3, 0, 0, $timezone)
                    ];
            $matchArray = $this->createSEMatches(4, $times);
        } else if ($this->type == 'se8') {
            $times = [  0 => Carbon::create(2019, 2, 17, 18, 15, 0, $timezone),
                        1 => Carbon::create(2019, 2, 17, 18, 15, 0, $timezone),
                        2 => Carbon::create(2019, 2, 17, 18, 15, 0, $timezone)
                    ];
            $matchArray = $this->createSEMatches(3, $times);
        } else if ($this->type == 'se32') {
            $times = [  0 => Carbon::create(2019, 2, 17, 1, 0, 0, $timezone),
                        1 => Carbon::create(2019, 2, 17, 2, 0, 0, $timezone),
                        2 => Carbon::create(2019, 2, 17, 3, 0, 0, $timezone),
                        3 => Carbon::create(2019, 2, 18, 1, 0, 0, $timezone),
                        4 => Carbon::create(2019, 2, 18, 3, 0, 0, $timezone)
                    ];
            $otherTime = Carbon::create(2019, 2, 18, 2, 0, 0, $timezone);
            $matchArray = $this->createSEMatches(5, $times);
            $matchArray[29]['wbp'] = $otherTime;
        } else if ($this->type == 'se64') {
            $times = [  0 => Carbon::create(2019, 2, 16, 18, 0, 0, $timezone),
                        1 => Carbon::create(2019, 2, 16, 19, 0, 0, $timezone),
                        2 => Carbon::create(2019, 2, 16, 20, 0, 0, $timezone),
                        3 => Carbon::create(2019, 2, 16, 21, 0, 0, $timezone),
                        4 => Carbon::create(2019, 2, 17, 19, 0, 0, $timezone),
                        5 => Carbon::create(2019, 2, 17, 21, 0, 0, $timezone)
                    ];
            $otherTime = Carbon::create(2019, 2, 17, 20, 0, 0, $timezone);
            $matchArray = $this->createSEMatches(6, $times);
            $matchArray[61]['wbp'] = $otherTime;
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
        //$this->teams()->detach();
    }

    public function createSEMatches($rounds, $timeArray)
    {
        $matchArray = [];
        $x = 0;
        for ($roundnumber = 0; $roundnumber < $rounds; $roundnumber++) {
            $time = $timeArray[$roundnumber];
            for ($i = 0; $i < 2**($rounds - $roundnumber - 1); $i++) {
                $wn = Match::encodePlayoffPosition(1,$roundnumber+2,floor($i/2)+1);
                if ($roundnumber+1 == $rounds) {
                    $wn = null;
                }
                $matchArray[$x] = [ 'pos' => Match::encodePlayoffPosition(1,$roundnumber+1,$i+1), 
                                    'wn' => $wn, 
                                    'ln' => null, 
                                    'teams' => [], 
                                    'wbp' => $time];
                $x++;
            }
        }
        return $matchArray;
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
        } else if ($this->type == 'playoffv3') {
            $tems = [];
            for ($i=1; $i <= 6; $i++) { 
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
            $mat[2] = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(2,1,1))->firstOrFail();
            $mat[3] = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(2,1,2))->firstOrFail();
            for ($i=1; $i <= 3; $i++) { 
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
        } else if ($this->type == 'se16' || $this->type == 'playoffv2' || $this->type == 'se8' || $this->type == 'se32' || $this->type == 'se64') {
            $tems = [];
            $teamcount = 16;
            $seedToMatch = [1 => 1,
                            8 => 2,
                            4 => 3,
                            5 => 4,
                            2 => 5,
                            7 => 6,
                            3 => 7,
                            6 => 8,
                ];
            if ($this->type == 'se8' || $this->type == 'playoffv2') {
                $teamcount = 8;
                $seedToMatch = [1 => 1,
                                4 => 2,
                                2 => 3,
                                3 => 4,
                ];
            }
            if ($this->type == 'se32') {
                $teamcount = 32;
                $seedToMatch = [1 => 1,
                                2 => 9,
                                3 => 13,
                                4 => 5,
                                5 => 7,
                                6 => 15,
                                7 => 11,
                                8 => 3,
                                9 => 4,
                                10 => 12,
                                11 => 16,
                                12 => 8,
                                13 => 6,
                                14 => 14,
                                15 => 10,
                                16 => 2,
                ];
            }
            if ($this->type == 'se64') {
                $teamcount = 64;
                $seedToMatch = [1   => 1,
                                32  => 2,
                                16  => 3,
                                17  => 4,
                                8   => 5,
                                25  => 6,
                                9   => 7,
                                24  => 8,
                                4   => 9,
                                29  => 10,
                                13  => 11,
                                20  => 12,
                                5   => 13,
                                28  => 14,
                                12  => 15,
                                21  => 16,
                                2   => 17,
                                31  => 18,
                                15  => 19,
                                18  => 20,
                                7   => 21,
                                26  => 22,
                                10  => 23,
                                23  => 24,
                                3   => 25,
                                30  => 26,
                                14  => 27,
                                19  => 28,
                                6   => 29,
                                27  => 30,
                                11  => 31,
                                22  => 32,
                ];
            }
            for ($i=1; $i <= $teamcount; $i++) { 
                $team = $this->teams()->where('seed', $i)->first();
                if ($team) {
                    $tems[$i] = $team;
                } else {
                    //Log::info('Adding BYE '. $i);
                    $tems[$i] = Team::where('title', 'BYE!')->firstOrFail();
                }
            }
            for ($i=1; $i <= floor($teamcount/2); $i++) { 
                $match = $this->matches()->where('playoff_position', Match::encodePlayoffPosition(1,1,$seedToMatch[$i]))->firstOrFail();
                $team1 = $tems[$i];
                $team2 = $tems[$teamcount+1-$i];
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
        } else if ($this->type == 'playoffv2') {
            $this->teams()->detach();
            $ga = $this->divisions()->where('title', 'Group A')->firstOrFail();
            $gb = $this->divisions()->where('title', 'Group B')->firstOrFail();
            $gc = $this->divisions()->where('title', 'Group C')->firstOrFail();
            $gd = $this->divisions()->where('title', 'Group D')->firstOrFail();

            $teams = $ga->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 1));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 5));

            $teams = $gb->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 2));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 6));

            $teams = $gc->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 3));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 7));

            $teams = $gd->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 4));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 8));
        } else if ($this->type == 'playoffv3') {
            $this->teams()->detach();
            $ga = $this->divisions()->where('title', 'Group A')->firstOrFail();
            $gb = $this->divisions()->where('title', 'Group B')->firstOrFail();

            $teams = $ga->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 1));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 3));
            $this->teams()->attach(array_values($teams)[2]['id'], array('seed' => 6));

            $teams = $gb->getTeamsSortedByScore()->toArray();
            $this->teams()->attach(array_values($teams)[0]['id'], array('seed' => 2));
            $this->teams()->attach(array_values($teams)[1]['id'], array('seed' => 5));
            $this->teams()->attach(array_values($teams)[2]['id'], array('seed' => 4));
        }
    }
}
