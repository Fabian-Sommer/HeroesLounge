<?php namespace Rikki\Heroeslounge\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Rikki\Heroeslounge\Models\Timeline as TimelineModel;

class Timeline extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\RelationController'];
    public $requiredPermissions = ['rikki.heroeslounge.timeline'];
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-timeline');
    }
}
