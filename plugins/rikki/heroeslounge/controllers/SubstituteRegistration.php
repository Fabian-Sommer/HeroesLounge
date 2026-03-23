<?php namespace Rikki\Heroeslounge\Controllers;


use Backend\Classes\Controller;
use BackendMenu;

use Rikki\Heroeslounge\Models\SubstituteRegistration as SubModel;

class SubstituteRegistration extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    public $requiredPermissions = ['rikki.heroeslounge.match'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-matches','manage-substitutes');
    }

}
