<?php namespace Rikki\Heroeslounge\Controllers;

 
use Backend\Classes\Controller;
use BackendMenu;

use Rikki\Heroeslounge\Models\Playoff as PlayoffModel;
use Rikki\Heroeslounge\Models\Timeline;
use Redirect;

class Playoff extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController', 'Backend\Behaviors\RelationController'];
    public $requiredPermissions = ['rikki.heroeslounge.season'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-seasons', 'manage-playoffs');
    }

    public function onMatchCreation()
    {
        $playoff = PlayoffModel::findOrFail(post('model_id'));
        $playoff->createMatches(post('year'), post('month'), post('day'), post('timezone'));
        $this->initForm($playoff);
        $this->initRelation($playoff, 'teams');
        return Redirect::refresh();
    }

    public function onTeamAssignment()
    {
        $playoff = PlayoffModel::findOrFail(post('model_id'));
        $playoff->seedTeams();
        $this->initForm($playoff);
        $this->initRelation($playoff, 'teams');
        return [
            '#teamPartial' => $this->relationRender('teams', ['readOnly' => false])
        ];
    }

    public function onTeamCollection()
    {
        $playoff = PlayoffModel::findOrFail(post('model_id'));
        $playoff->getSeedsFromGroups();
        $this->initForm($playoff);
        $this->initRelation($playoff, 'teams');
        return Redirect::refresh();
    }
}
