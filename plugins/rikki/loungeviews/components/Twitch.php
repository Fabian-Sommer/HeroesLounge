<?php namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;

use Request;

class Twitch extends ComponentBase
{
    public $timeline = null;

    public function componentDetails()
    {
        return [
            'name' => 'Twitch',
            'description' => 'Displays a specific Twitch channel if online.'
        ];
    }

    public $chan = null;
    public $height = null;
    public $width = null;

    public function onRun()
    {
        $this->chan = $this->property('channel');
        $this->height = $this->property('height');
        $this->width = $this->property('width');
        
        $this->addJs('https://player.twitch.tv/js/embed/v1.js');
    }

    public function defineProperties()
    {
        return [
            'channel' => [
                'title' => 'Channel',
                'description' => 'Channel Name (e.g. heroes_lounge) to show',
                'default' => 'heroes_lounge',
                'type' => 'string',
                'required' => true
            ],
            'height' => [
                'title' => 'Height',
                'description' => 'Height(-90px) if not use full height',
                'default' => 500,
                'type' => 'string'
            ],
            'width' => [
                'title' => 'Width',
                'description' => 'Width in % if not 100%',
                'default' => 'heroes_lounge',
                'default' => 80,
                'type' => 'string',
            ],
        ];
    }
}
