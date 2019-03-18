<?php namespace Rikki\Heroeslounge\Controllers;

 
use Backend\Classes\Controller;
use BackendMenu;

use Rikki\Heroeslounge\Models\Division as DivisionModel;
use Rikki\Heroeslounge\Models\Timeline;

class Division extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\RelationController'];
    public $requiredPermissions = ['rikki.heroeslounge.division'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-seasons', 'manage-divisions');
    }

    public function onTimelineCreation()
    {
        $division = DivisionModel::findOrFail(post('model_id'));
        $timeline = new Timeline();
        $timeline->type = 'Invalid.Type';
        $timeline->message = 'Admin message';
        $timeline->save();
        $timeline->divisions()->add($division);
        $this->initForm($division);
        $this->initRelation($division, 'timeline');
        return [
            '#timelinePartial' => $this->relationRender('timeline', ['readOnly' => false])
        ];
    }
}
