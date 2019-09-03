<?php namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;
use RainLab\User\Components\Account as UserAccount;
use RainLab\User\Models\User as UserModel;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Auth;
use Db;

class Profile extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Profile',
            'description' => 'Allows closer inspection of a user\'s details on the front end'
        ];
    }

    public $sloth = null;
    public $user = null;

    public function init()
    {
        $this->user = Auth::getUser();
        
        $this->addCss('/plugins/martin/ssbuttons/assets/css/social-sharing-nb.css');
        $this->sloth =  SlothModel::find($this->param('id'));
        if($this->sloth)
        {
            $component = $this->addComponent(
                'Rikki\LoungeViews\Components\TimelineEntries',
                'timeLine',
                [
                    'deferredBinding'   => true,
                    'maxItems' => $this->property('maxTimelineEntries'),
                    'type' => 'sloth'
                ]
            );

            $component = $this->addComponent(
                'Rikki\LoungeStatistics\Components\SlothStatistics',
                'slothStatistics',
                [
                    'deferredBinding'   => true,
                    'sloth_id' => $this->sloth->id
                ]
            );
        }
      
    }



    public function defineProperties()
    {
        return [
            'maxTimelineEntries' => [
                'title' => 'MaxTimelineEntries',
                'description' => 'The most amount of timeline entries to show',
                'default' => 5,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Timeline Entries property can contain only numeric symbols'
            ]
        ];
    }
}
