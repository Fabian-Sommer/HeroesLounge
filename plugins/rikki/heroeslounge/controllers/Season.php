<?php namespace Rikki\Heroeslounge\Controllers;

 
use Backend\Classes\Controller;
use BackendMenu;

use Rikki\Heroeslounge\Models\Season as SeasonModel;
use Rikki\Heroeslounge\Models\Timeline;

class Season extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController','Backend\Behaviors\RelationController'];
    public $requiredPermissions = ['rikki.heroeslounge.season'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-seasons', 'manage-seasons');
    }

    public function onTimelineCreation()
    {
        $season = SeasonModel::findOrFail(post('model_id'));
        $timeline = new Timeline();
        $timeline->type = 'Invalid.Type';
        $timeline->message = 'Admin message';
        $timeline->save();
        $timeline->seasons()->add($season);
        $this->initForm($season);
        $this->initRelation($season, 'timeline');
        return [
            '#timelinePartial' => $this->relationRender('timeline', ['readOnly' => false])
        ];
    }
}
