<?php namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Bans as Ban;

class Bans extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Bans',
            'description' => 'Visualizes current bans entered in the backend'
        ];
    }

    public $heroes = null;
    public $talents = null;
    public $literals = null;

    public function init()
    {
        $this->heroes = Ban::whereNotNull('hero_id')->whereNull('talent_id')->get();
        $this->talents = Ban::whereNotNull('talent_id')->get();
        $this->literals = Ban::whereNotNull('literal')->whereNull('hero_id')->whereNull('talent_id')->get();
    }



    public function defineProperties()
    {
        return [

        ];
    }
}
