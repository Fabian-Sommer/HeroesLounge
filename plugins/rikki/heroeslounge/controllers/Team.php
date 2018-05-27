<?php namespace Rikki\Heroeslounge\Controllers;

 
use Backend\Classes\Controller;
use BackendMenu;

use Rikki\Heroeslounge\Models\Team as TeamModel;
use Rikki\Heroeslounge\Models\Sloth as Sloths;
use Rikki\Heroeslounge\Models\Timeline;
use Redirect;

class Team extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController','Backend\Behaviors\RelationController'];
    public $requiredPermissions = ['rikki.heroeslounge.team'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-teams','manage-teams');
    }

    public function onTimelineCreation()
    {
        $team = TeamModel::findOrFail(post('model_id'));
        $timeline = new Timeline();
        $timeline->type = 'Invalid.Type';
        $timeline->message = 'Admin message';
        $timeline->save();
        $timeline->teams()->add($team);
        $this->initForm($team);
        $this->initRelation($team, 'timeline');
        return [
            '#timelinePartial' => $this->relationRender('timeline', ['readOnly' => false])
        ];
    }

    public function formAfterUpdate($model)
    {
        $dis = post('Team[disbanded]');
        if($dis == true) {
            $team = $model;
            $team->sloths->each(function($sloth){
                if($sloth->is_captain)
                    $sloth->is_captain = false;

                $sloth->team()->dissociate();
                $sloth->save();

            });      
            $team->divisions->each(function($d) use(&$team) {
                if ($d->season != null && $d->season->is_active) {
                    $t = $d->teams()->where('rikki_heroeslounge_teams.id',$team->id)->first();
                    if($t) {
                        $t->pivot->active = false;
                        $t->pivot->save();
                    }
                }
                
            }); 
        }
    }
  

}
