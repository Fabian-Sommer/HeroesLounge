<?php namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Classes\Helpers\TimezoneHelper;

use Redirect;

class SetTimezone extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Set Timezone',
            'description' => 'Has the functionality to set the session timezone from client side js'
        ];
    }

    public $hasTimezone = false;

    public function onRender() {
        $this->hasTimezone = TimezoneHelper::hasTimezone();
    }

    public function onTimezoneDetection() {
        return TimezoneHelper::setTimezone();
    }

    public function defineProperties()
    {
        return [];
    }
}
