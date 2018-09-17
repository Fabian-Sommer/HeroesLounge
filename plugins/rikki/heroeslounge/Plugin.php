<?php namespace Rikki\Heroeslounge;

use Illuminate\Support\Facades\Event;
use Rikki\Heroeslounge\Models\Timeline;
use SuperClosure\Analyzer\Visitor\ThisDetectorVisitor;
use RainLab\Blog\Classes\TagProcessor;
use System\Classes\PluginBase;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Controllers\Users as UsersController;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Match as MatchModel;
use Rikki\Heroeslounge\Models\Game as Games;
use Rikki\Heroeslounge\Models\Map as Maps;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\classes\Matchmaking\Swiss;
use Rikki\Heroeslounge\classes\MMR as MMR;
use Rikki\Heroeslounge\classes\hotslogs as hotslogs;
use Rikki\Heroeslounge\classes\Mailchimp;
use Rikki\Heroeslounge\classes\Heroes\HeroUpdater;
use Rikki\Heroeslounge\classes\deployment\Deployment;
use Rikki\Heroeslounge\classes\helpers\NotificationHelper;
use Rikki\Heroeslounge\classes\Discord\Attendance;
use Indikator\content\models\Blog as Blog;

use Session;
use Flash;
use BackendAuth;

class Plugin extends PluginBase
{

    public $require = ['Indikator.Content'];
    
    public function registerComponents()
    {
        return [
            'Rikki\Heroeslounge\Components\ManageTeam' => 'ManageTeam',
            'Rikki\Heroeslounge\Components\ManageMatches' => 'ManageMatches',
            'Rikki\Heroeslounge\Components\ScheduleMatch' => 'ScheduleMatch',
            'Rikki\Heroeslounge\Components\CreateTeam' => 'CreateTeam',
            'Rikki\Heroeslounge\Components\RoundMatches' => 'RoundMatches',
            'Rikki\Heroeslounge\Components\UpcomingMatches' => 'UpcomingMatches',
            'Rikki\Heroeslounge\Components\CasterSchedule' => 'CasterSchedule',
            'Rikki\Heroeslounge\Components\UpdateMatch' => 'UpdateMatch',
            'Rikki\Heroeslounge\Components\SlothAccount' => 'SlothAccount',
            'Rikki\Heroeslounge\Components\SlothResetPassword' => 'SlothResetPassword',
            'Rikki\Heroeslounge\Components\CreateApp' => 'CreateApp',
            'Rikki\Heroeslounge\Components\ViewApps' => 'ViewApps',
            'Rikki\Heroeslounge\Components\RssFeed' => 'RssFeed',
        ];
    }

    public function registerSettings()
    {
    }

  

    public function register()
    {
        /* Will have to change for new blog component
        TagProcessor::instance()->registerCallback(function ($input, $preview) {
            return preg_replace(
                '/\[tweet\]([0-9\)]+)/',
                '<blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr"></p><a href="https://twitter.com/hl_embed/status/$1">embedded_tweet</a></blockquote>',
            $input
            );
        });*/
    }

    public function registerSchedule($schedule)
    {
        $schedule->call(function () {
            $ss = Seasons::where('is_active', 1)->where('reg_open', 0)->where('mm_active', 1)->get();
            $ss->each(function ($s) {
                $mm = new Swiss;
                $mm->prepare($s);
            });
        })->weekly()->mondays()->at('01:00');

        $schedule->call(function () {
            MMR\MMRFetcher::updateMMRs();
        })->dailyAt('3:00');

        $schedule->call(function () {
            hotslogs\IDFetcher::fetchIDs();
        })->dailyAt('3:30');

        $schedule->call(function () {
            $hu = new HeroUpdater;
            $hu->updateHeroes();
            Deployment::updateHeroprotocol();
        })->dailyAt('4:00');
    }


    public function boot()
    {
        Event::listen('rainlab.user.login', function ($user) {
            $msg = NotificationHelper::generateMessages($user);
            Session::put('notifications',$msg);
        });


        Event::listen('offline.sitesearch.query', function ($query) {
            $items = SlothModel::where('title', 'like', "%${query}%")->get();
            $results = $items->map(function ($item) use ($query) {
                $relevance = mb_stripos($item->title, $query) !== false ? 2: 1;
                return [
                    'title' => $item->title,
                    'text' => $item->short_description,
                    'url' => 'user/view/'.$item->user->id,
                    'thumb' => $item->user->avatar,
                    'relevance' => $relevance
                ];
            });

            return [
                'provider' => 'Sloth Profile',
                'results' => $results
            ];
        });
        Event::listen('offline.sitesearch.query', function ($query) {
            $items = Teams::where('title', 'like', "%${query}%")->get();
            $results = $items->map(function ($item) use ($query) {
                $relevance = mb_stripos($item->title, $query) !== false ? 2: 1;
                return [
                    'title' => $item->title,
                    'text' => 'Some cool content I yet have to come up with',
                    'url' => 'team/view/'.$item->slug,
                    'thumb' => $item->logo,
                    'relevance' => $relevance
                ];
            });

            return [
                'provider' => 'Team Profile',
                'results' => $results
            ];
        });
        Event::listen('offline.sitesearch.query', function ($query) {
            $items = Maps::where('title', 'like', "%${query}%")->get();
            $results = $items->map(function ($item) use ($query) {
                $relevance = mb_stripos($item->titel, $query) !== false ? 2 : 1;
                foreach ($item->matches()->get() as $match) {
                    return [
                            'title' => $match->teams[0]->title.' VS '.$match->teams[1]->title,
                            'relevance' => $relevance
                        ];
                }
            });
            return
            [
                'provider' => 'MapMatches',
                'results' => $results
            ];
        });


    


        Event::listen('eloquent.saved: System\Models\File', function ($event) {
            if ($event->field == 'logo') {
                switch ($event->attachment_type) {
                    case 'Rikki\Heroeslounge\Models\Team':
                        $timeline = new Timeline();
                        $timeline->type = 'Team.Logo';
                        $timeline->save();
                        Teams::findOrFail($event->attachment_id)->timeline()->add($timeline);
                        break;
                }
            }
        });
        Blog::extend(function($model)
        {

            $model->addDynamicMethod('findAuthor',function() use ($model)
            {
                $m = BackendAuth::createUserModel();
                $q = $m->newQuery();
                BackendAuth::extendUserQuery($q);
                $user = $q->find($model->author_id);
                return $user ?: null;
            });

            $model->addDynamicMethod('nextPost',function() use ($model)
            {
                return Blog::isPublished()
                ->where('id', '>' , $model->id)
                ->orderBy('id', 'asc')
                ->first();
            });

            $model->addDynamicMethod('previousPost',function() use ($model)
            {
                return Blog::isPublished()
                ->where('id', '<' , $model->id)
                ->orderBy('id', 'desc')
                ->first();
          
            });
        });

      
        UserModel::extend(function ($model) {
            $model->hasOne['sloth'] = ['Rikki\Heroeslounge\Models\Sloth', 'delete' => true];
        });

        UsersController::extendFormFields(function ($form, $model, $context) {
            if (!$model instanceof UserModel) {
                return;
            }

            if (!$model->exists) {
                return;
            }

            SlothModel::getFromUser($model);

            $form->addTabFields([
                'sloth[title]' => [
                    'label' => 'Title',
                     'tab' => 'Sloth',
                     'type' => 'text'
                ],
                'sloth[discord_tag]' => [
                    'label' => 'Discord Tag',
                     'tab' => 'Sloth',
                     'type' => 'text'
                ],
                'sloth[battle_tag]' => [
                    'label' => 'BattleTag',
                     'tab' => 'Sloth',
                     'type' => 'text'
                ],
                'sloth[team]' => [
                    'label' => 'Team',
                    'type' => 'recordfinder',
                    'list' => '$/rikki/heroeslounge/models/team/columns.yaml',
                    'prompt' => 'Click the %s button to find a team',
                    'nameFrom' => 'title',
                    'tab' => 'Sloth'
                ],
                'sloth[role]' => [
                    'label' => 'Role',
                    'type' => 'relation',
                    'nameFrom' => 'title',
                    'tab' => 'Sloth'
                ], 'sloth[mmr]' => [
                    'label' => 'Weighted MMR',
                    'tab' => 'Sloth',
                    'type' => 'number',
                    'readOnly' => true
                ], 'sloth[all_mmr]' => [
                    'label' => 'All available MMR',
                    'comment' => 'Is QM if nothing else is available',
                    'tab' => 'Sloth',
                    'type' => 'number',
                    'readOnly' => true
                ],
                'sloth[birthday]' => [
                    'label' => 'Birthday',
                     'tab' => 'Sloth',
                     'type' => 'datepicker',
                     'mode' => 'date'
                ],
                'sloth[banner]' => [
                    'label' => 'Banner',
                    'tab' => 'Sloth',
                    'type' => 'fileupload'
                ],
                'sloth[short_description]' => [
                    'label' => 'Short Description',
                     'tab' => 'Sloth',
                     'type' => 'textarea'
                ],
                'sloth[twitch_url]' => [
                    'label' => 'Twitch URL',
                     'tab' => 'Sloth',
                     'type' => 'text'
                ],
                'sloth[twitter_url]' => [
                    'label' => 'Twitter URL',
                     'tab' => 'Sloth',
                     'type' => 'text'
                ],
                'sloth[facebook_url]' => [
                    'label' => 'Facebook URL',
                     'tab' => 'Sloth',
                     'type' => 'text'
                ],
                'sloth[youtube_url]' => [
                    'label' => 'YouTube URL',
                     'tab' => 'Sloth',
                     'type' => 'text'
                 ],
                'sloth[website_url]' => [
                    'label' => 'Website URL',
                     'tab' => 'Sloth',
                     'type' => 'text'
                 ]
                ]);
        });
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
            ]
        ];
    }
}
