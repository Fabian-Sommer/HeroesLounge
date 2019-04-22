<?php namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;

use Cookie;

class SpoilersToggle extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Spoilers Toggle',
            'description' => 'Turns spoilers display on or off'
        ];
    }

    public $showSpoilers = 0;
    public $callbackName = null;

    public function onRender() {
        $this->showSpoilers = (int)Cookie::get('showSpoilers');
        $this->callbackName = $this->property('callback');
    }

    public function onSetShowSpoilers() {
        $this->showSpoilers = (int)$_POST['showSpoilers'] > 0 ? 1 : 0;
        Cookie::queue('showSpoilers', $this->showSpoilers, 60*24*30);
        return [
            'showSpoilers' => $this->showSpoilers,
        ];
    }

    public function defineProperties()
    {
        return [
            'callback' => [
                'title' => 'Callback function',
                'description' => 'The name of the callback function to invoke when show spoilers is set',
                'default' => null,
                'type' => 'string',
                // https://stackoverflow.com/questions/2008279/validate-a-javascript-function-name
                'validationPattern' => '^[$a-zA-Z_][0-9a-zA-Z_$]*$',
                'validationMessage' => 'The callback property must be a valid javascript function name'
            ],
        ];
    }
}
