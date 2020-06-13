<?php namespace Rikki\Heroeslounge\Controllers;


use Backend\Classes\Controller;
use BackendMenu;

use Rikki\Heroeslounge\Models\Team as TeamModel;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\SlothTeamPivot;
use Rikki\Heroeslounge\Models\Timeline;
use Rikki\Heroeslounge\classes\Discord;
use Log;
use Redirect;
use Carbon\Carbon;
use Db;

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
                Db::table('rikki_heroeslounge_sloth_team')->where('team_id', $team->id)->where('sloth_id', $sloth->id)->where('deleted_at', NULL)->update(['deleted_at' => Carbon::now()]);

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

    public function onRelationButtonRemove()
    {
        if ($_POST['_relation_field'] == 'sloths') {
            foreach ($_POST['checked'] as $key => $sloth_id) {
                $team = TeamModel::where('title', $_POST['Team']['title'])->first();
                $sloth = Sloth::findOrFail($sloth_id);
                Db::table('rikki_heroeslounge_sloth_team')->where('team_id', $team->id)->where('sloth_id', $sloth_id)->where('deleted_at', NULL)->update(['deleted_at' => Carbon::now()]);
                $timeline = new Timeline();
                $timeline->type = 'Sloth.Left.Team';
                $timeline->save();
                $timeline->sloths()->add($sloth);
                $timeline->teams()->add($team);
            }
        } else { 
            Controller::onRelationButtonRemove();
        }
    }

    public function onRelationManagePivotUpdate($model)
    {
        if ($_POST['_relation_field'] == 'divisions') {
            $divisionId = $_POST['manage_id'];
            $newActivityStatus = $_POST['Division']['pivot']['active'];
            $team = TeamModel::findOrFail($model);

            $updatedModel = $team->divisions->first(function($division) use($divisionId) {
                return $division->id == $divisionId;
            });

            if ($updatedModel && $updatedModel->pivot->active != $newActivityStatus ) {
                if ($newActivityStatus) {
                    $team->_saveTimelineEntry('Team.rejoinedDivision');
                } else if (!$newActivityStatus) {
                    $team->_saveTimelineEntry('Team.leftDivision');
                }
            }
        }

        Controller::onRelationManagePivotUpdate();
    }

    public function onAddSloth()
    {
        $team = TeamModel::find(post('model_id'));
        $sloth = Sloth::where('title', post('sloth_title'))->firstOrFail();
        $team_id = $team->id;
        if (!$sloth->teams->contains(function ($team) use ($team_id) {
            return $team->id == $team_id;
        })) {
            Db::insert('insert into rikki_heroeslounge_sloth_team (sloth_id, team_id, created_at, updated_at) values (?, ?, ?, ?)', [$sloth->id, $team->id, Carbon::now(), Carbon::now()]);
            $timeline = new Timeline();
            $timeline->type = 'Sloth.Joins.Team';
            $timeline->save();
            $timeline->sloths()->add($sloth);
            $timeline->teams()->add($team);
        }
        return Redirect::refresh();
    }

}
