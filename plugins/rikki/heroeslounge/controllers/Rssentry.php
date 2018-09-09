<?php namespace Rikki\Heroeslounge\Controllers;

 
use Backend\Classes\Controller;
use BackendMenu;

use Rikki\Heroeslounge\Models\RssEntry as RssModel;

class RssEntry extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\RelationController'];
    public $requiredPermissions = ['indikator.content.blog'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Indikator.Content', 'content', 'rssentry');
    }

}
