<?php namespace Rikki\Heroeslounge\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Playoff Redirect Back-end Controller
 */
class PlayoffRedirect extends Controller
{
    public $requiredPermissions = ['rikki.heroeslounge.season', 'rikki.heroeslounge.single_playoff'];
    public function __construct()
    {
        parent::__construct();        
        BackendMenu::setContext('Rikki.Heroeslounge', 'heroeslounge', 'playoffredirect');
    }

    public function index(){
        if (!$this->user->hasAccess(['rikki.heroeslounge.season'])) {
            return redirect('/backend/rikki/heroeslounge/playoff');
        }
        return redirect('/backend/rikki/heroeslounge/season');
    }
}
