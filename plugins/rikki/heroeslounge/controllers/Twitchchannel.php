<?php namespace Rikki\Heroeslounge\Controllers;

 
use Backend\Classes\Controller;
use BackendMenu;

class Twitchchannel extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    public $requiredPermissions = ['rikki.heroeslounge.twitchchannel'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-general', 'manage-twitchchannels');
    }
}
