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

class CreateTestUsers
{
    public function createSloths()
    {
        for ($i=1;$i<100;$i++) {
            $mail = 'test'.$i.'@sloth.com';
            $user = Auth::register(['email' => $mail,'password' => 'test','password_confirmation' => 'test','username' => 'test'.$i]);
            $sloth = Sloth::getFromUser($user);
            $sloth->title = 'test'.$i;
            $sloth->user->avatar = 'plugins/rikki/heroeslounge/assets/img/tests/teams/logos/'.$i.'.png';
            $sloth->user->save();
            $sloth->banner = 'plugins/rikki/heroeslounge/assets/img/tests/teams/banners/'.$i.'.jpg';
            $sloth->twitter_url  = URLHelper::makeTwitterURL('https://twitter.com/HeroesLoungeGG');
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
