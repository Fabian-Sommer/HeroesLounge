<?php namespace Rikki\Heroeslounge\Controllers;


use Backend\Classes\Controller;
use BackendMenu;

use Rikki\Heroeslounge\Models\Team as TeamModel;
use Rikki\Heroeslounge\Models\Sloth as Sloths;
use Rikki\Heroeslounge\Models\Timeline;
use Redirect;

use Rikki\Heroeslounge\classes\Discord;

class Team extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\RelationController'];
    public $requiredPermissions = ['rikki.heroeslounge.team'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
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
            $team->sloths->each(function($sloth) use ($team) {
                $isCaptain = $sloth->isCaptain();

                $sloth->teams()->remove($team);
                $sloth->save();

                if ($isCaptain != $sloth->isCaptain()) {
                    $sloth->removeDiscordCaptainRole();
                }

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
            $team->seasons()->detach();
        }
    }

    public function onRelationManagePivotUpdate($model) {
        $data = post();
        $team = TeamModel::find($model);

        foreach ($team->sloths as $sloth) {
            if ($sloth->pivot->sloth_id == $data['manage_id']) {
                $sloth->pivot->is_captain = $data['Sloth']['pivot']['is_captain'];
                $sloth->pivot->save();

                /*
                    Add Discord captain updating logic here.
                */
            }
        }
    }

}
