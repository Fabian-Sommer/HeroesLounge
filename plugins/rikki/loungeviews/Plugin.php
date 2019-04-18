<?php namespace Rikki\LoungeViews;

use System\Classes\PluginBase;

use Flash;

class Plugin extends PluginBase
{
    public $require = ['Rikki.Heroeslounge'];

    public function registerComponents()
    {
        return [
            'Rikki\LoungeViews\Components\DivisionOverview' => 'DivisionOverview',
            'Rikki\LoungeViews\Components\RecentResults' => 'RecentResults',
            'Rikki\LoungeViews\Components\PlayoffOverview' => 'PlayoffOverview',
            'Rikki\LoungeViews\Components\Bans' => 'Bans',
            'Rikki\LoungeViews\Components\SeasonOverview' => 'SeasonOverview',
            'Rikki\LoungeViews\Components\ViewTeam' => 'ViewTeam',
            'Rikki\LoungeViews\Components\ViewMatch' => 'ViewMatch',
            'Rikki\LoungeViews\Components\Twitch' => 'Twitch',
            'Rikki\LoungeViews\Components\Profile' => 'Profile',
            'Rikki\LoungeViews\Components\Navigation' => 'Navigation',
            'Rikki\LoungeViews\Components\SideNav' => 'SideNav',
            'Rikki\LoungeViews\Components\ViewApplication' => 'ViewApplication',
            'Rikki\LoungeViews\Components\TimelineEntries' => 'TimelineEntries',
            'Rikki\LoungeViews\Components\ParticipationOverview' => 'ParticipationOverview',
            'Rikki\LoungeViews\Components\DivisionTable' => 'DivisionTable',
            'Rikki\LoungeViews\Components\ExtendedDivisionTable' => 'ExtendedDivisionTable',
            'Rikki\LoungeViews\Components\BlogPage' => 'BlogPage',
            'Rikki\LoungeViews\Components\BlogFeatured' => 'BlogFeatured',
        ];
    }

    public function registerSettings()
    {
    }

    public function registerPermissions()
    {
        return [
        ];
    }

    public function register()
    {
      
    }

    public function registerSchedule($schedule)
    {
    }

    public function boot()
    {
    }

    public function registerMarkupTags()
    {
    }
}
