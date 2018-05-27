<?php namespace Rikki\Heroeslounge\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Bans extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $requiredPermissions = ['rikki.heroeslounge.bans'];
    
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-general', 'manage-bans');
    }


}