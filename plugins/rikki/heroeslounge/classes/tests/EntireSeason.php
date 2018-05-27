<?php namespace Rikki\Heroeslounge\classes\tests;

  
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Timeline;
use Rikki\Heroeslounge\Models\Season;
use Rikki\Heroeslounge\Models\Division;
use Rikki\Heroeslounge\Models\Game;
use Rikki\Heroeslounge\Models\Apps;
use Rikki\Heroeslounge\Models\SlothRole;

use Auth;
use Rikki\Heroeslounge\Classes\Helpers\URLHelper;
use Rikki\Heroeslounge\classes\Matchmaking\Swiss;
use RainLab\User\Models\User;

class EntireSeason
{
    public $casterSloths = null;
    public function prepare()
    {
        $t = Team::all();
        $t->each(function ($item, $key) {
            $item->divisions()->detach();
            $item->matches()->detach();
            $item->delete();
        });
        Team::truncate();
        Sloth::truncate();
        $m = Match::all();
        $m->each(function ($item, $key) {
            $item->casters()->detach();
            $item->delete();
        });
        Match::truncate();
        $timeline = Timeline::all();
        $timeline->each(function ($item, $key) {
            $item->divisions()->detach();
            $item->matches()->detach();
            $item->sloths()->detach();
            $item->teams()->detach();
            $item->seasons()->detach();
            $item->delete();
        });
        Timeline::truncate();
        Season::truncate();
        Division::truncate();
        Game::truncate();
        Apps::truncate();
    
        $this->createCasterSloths();
        $this->createTeams();
    }

    public function createSeason($i)
    {
        $s = new Season;
        $s->title = "Season ".$i;
        $s->is_active = 1;
        $s->mm_active = 1;
        $s->save();
        $s->round_length = 7;
        $r = 2;

        for ($ii =1;$ii <= $r;$ii++) {
            $d = $this->createDivision($ii);
            $s->divisions()->add($d);
        }
        
        $teams= Team::all();
        if ($r==2) {
            $t1 = Team::take(15)->get();
            $t2 = Team::skip(15)->take(15)->get();
            $divs = $s->divisions()->get();
            foreach ($t1 as $team) {
                $divs[1]->teams()->add($team);
            }
            $divs[1]->save();
           
            foreach ($t2 as $team) {
                $divs[0]->teams()->add($team);
            }
            $divs[0]->save();
        } else {
            $t1 = $teams->take(10);
            $t2 = Team::skip(10)->take(10);
            $t3 = Team::skip(20)->take(10);
            $divs = $s->divisions()->get();
            
            foreach ($t1 as $team) {
                $divs[0]->teams()->add($team);
            }
            $divs[0]->save();
           
            foreach ($t2 as $team) {
                $divs[1]->teams()->add($team);
            }
            $divs[1]->save();
           
            foreach ($t3 as $team) {
                $divs[2]->teams()->add($team);
            }
            $divs[2]->save();
        }

        $s->is_active = true;
        $s->save();
        $this->runMM($s);
    }

    public function createCasterSloths()
    {
        $this->casterSloths = [];
        for ($i=1;$i<=5;$i++) {
            $mail = 'testcaster'.$i.'@sloth.com';
            $user = Auth::register(['email' => $mail,'password' => 'test','password_confirmation' => 'test','username' => 'testcaster'.$i]);
            $sloth = Sloth::getFromUser($user);
            $sloth->title = 'test'.$i;
            $sloth->user->avatar = 'plugins/rikki/heroeslounge/assets/img/tests/teams/logos/'.$i.'.png';
            $sloth->user->save();
            $sloth->banner = 'plugins/rikki/heroeslounge/assets/img/tests/teams/banners/'.$i.'.jpg';
            $sloth->twitter_url  = URLHelper::makeTwitterURL('https://twitter.com/hotslounge');
            $sloth->role_id = SlothRole::where('title', 'Support')->firstOrFail()->id;
            $sloth->twitch_url  = URLHelper::makeTwitchURL('twitch.tv/blizzheroes');
            $sloth->facebook_url  = URLHelper::makeFacebookURL('https://www.facebook.com/klaasjan.boon');
            $sloth->youtube_url  = URLHelper::makeYoutubeURL('https://www.youtube.com/channel/UCbo4u4GMHlNPIVB4PPfyJZg');
            $sloth->website_url = URLHelper::makeWebsiteURL('http://www.heroeslounge.gg');

            $sloth->short_description = $sloth->title.'s short description. Lorem Ipsum dolor sit amet. Lorem Ipsum dolor sit amet.';
            $sloth->save();
            $this->casterSloths[] = $sloth;
        }
    }

    public function createDivision($i)
    {
        $d = new Division;
        $d->title = "Division ".$i;
        $d->save();
        return $d;
    }

    public function runMM($s)
    {
        $t = new Swiss;
        for ($i=1;$i<=$s->round_length;$i++) {
            $t->prepare($s);
            foreach ($s->divisions()->get() as $div) {
                $this->setResults($div, $i);
            }
        }
    }

    public function setResults($d, $round)
    {
        $winner = array();
        foreach ($d->matches()->where('round', $round)->get() as $match) {
            if ($match->is_played != 1) {
                $cast = rand(0, 10);
                if ($cast < 5) {
                    $pivot = ['approved' => 1];
                    $match->casters()->add($this->casterSloths[$cast], $pivot);
                }
                $rnd = rand(0, 100);
               
                if ($rnd <= 50) {
                    $match->winner = $match->teams[0];
                } else {
                    $match->winner = $match->teams[1];
                }

                $match->is_played = 1;

                for ($i = 0; $i < 2;$i++) {
                    $m = rand(1, 13);
                    $g = new Game;
                    $g->match_id = $match->id;
                    $g->map_id = $m;
                    if ($rnd <= 50) {
                        $g->winner_id = $match->teams[0]->id;
                    } else {
                        $g->winner_id = $match->teams[1]->id;
                    }
                    $g->save();
                }
               
                $match->teams[0]->pivot->save();
                $match->teams[1]->pivot->save();
                $match->save();
            }
        }
    }



    public function createTeams()
    {
        for ($i=1;$i<31;$i++) {
            $team = new Team;
            $team->title = "team".$i;
            $team->short_description = "Shortdescription team ".$i;
            $team->logo = 'plugins/rikki/heroeslounge/assets/img/tests/teams/logos/'.$i.'.png';
            $team->banner = 'plugins/rikki/heroeslounge/assets/img/tests/teams/banners/'.$i.'.jpg';
            $team->twitter_url  = URLHelper::makeTwitterURL('https://twitter.com/hotslounge');
            $team->twitch_url  = URLHelper::makeTwitchURL('twitch.tv/blizzheroes');
            $team->facebook_url  = URLHelper::makeFacebookURL('https://www.facebook.com/klaasjan.boon');
            $team->youtube_url  = URLHelper::makeYoutubeURL('https://www.youtube.com/channel/UCbo4u4GMHlNPIVB4PPfyJZg');
            $team->website_url = URLHelper::makeWebsiteURL('http://www.heroeslounge.gg');
            $team->save();
            $this->createSloths($team);
        }

        $team = new Team;
        $team->title = "BYE!";
        $team->short_description = "Shortdescription team ".$i;
        $team->logo = 'plugins/rikki/heroeslounge/assets/img/tests/teams/logos/'.$i.'.png';
        $team->banner = 'plugins/rikki/heroeslounge/assets/img/tests/teams/banners/'.$i.'.jpg';
        $team->twitter_url  = URLHelper::makeTwitterURL('https://twitter.com/hotslounge');
        $team->twitch_url  = URLHelper::makeTwitchURL('twitch.tv/blizzheroes');
        $team->facebook_url  = URLHelper::makeFacebookURL('https://www.facebook.com/klaasjan.boon');
        $team->youtube_url  = URLHelper::makeYoutubeURL('https://www.youtube.com/channel/UCbo4u4GMHlNPIVB4PPfyJZg');
        $team->website_url = URLHelper::makeWebsiteURL('http://www.heroeslounge.gg');
        $team->save();

        $this->createSeason(1);
    }


    public function createSloths($team)
    {
        for ($i=1;$i<2;$i++) {
            $mail = 'Sloth'.$i.$team->title.'@sloth'.$i.'.com';
            $user = Auth::register(['email' => $mail,'password' => 'test','password_confirmation' => 'test','username' => 'Sloth '.$i.$team->title]);
            $sloth = Sloth::getFromUser($user);
            $sloth->title = 'Sloth '.$i.$team->title;
            $sloth->team_id = $team->id;
            $i == 1 ? $sloth->is_captain = 1 : $sloth->is_captain = 0;
            $sloth->user->avatar = 'plugins/rikki/heroeslounge/assets/img/tests/teams/logos/'.$i.'.png';
            $sloth->user->save();
            $sloth->banner = 'plugins/rikki/heroeslounge/assets/img/tests/teams/banners/'.$i.'.jpg';
            $sloth->twitter_url  = URLHelper::makeTwitterURL('https://twitter.com/hotslounge');
            $sloth->role_id = SlothRole::where('title', 'Support')->firstOrFail()->id;
            $sloth->twitch_url  = URLHelper::makeTwitchURL('twitch.tv/blizzheroes');
            $sloth->facebook_url  = URLHelper::makeFacebookURL('https://www.facebook.com/klaasjan.boon');
            $sloth->youtube_url  = URLHelper::makeYoutubeURL('https://www.youtube.com/channel/UCbo4u4GMHlNPIVB4PPfyJZg');
            $sloth->website_url = URLHelper::makeWebsiteURL('http://www.heroeslounge.gg');

            $sloth->short_description = $sloth->title.'s short description. Lorem Ipsum dolor sit amet. Lorem Ipsum dolor sit amet.';
            $sloth->save();
        }
    }
}
