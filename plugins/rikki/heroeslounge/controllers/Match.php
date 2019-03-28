<?php namespace Rikki\Heroeslounge\Controllers;

 
use Backend\Classes\Controller;
use BackendMenu;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\Models\Timeline;
use Rikki\Heroeslounge\Models\Match as MatchModel;
use Rikki\Heroeslounge\Models\Game;
use Rikki\Heroeslounge\Models\Division;

class Match extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController',
    'Backend\Behaviors\RelationController'];
    public $requiredPermissions = ['rikki.heroeslounge.match'];
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $listConfig = 'config_list.yaml';
    
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-matches','manage-matches');
    }

    public static function formExtendFields($form)
    {
        $form->addFields([
            'division@create' => [
                'label' => 'Division',
                'span' => 'right',
                'type' => 'dropdown',
                'options' => Division::listDivisionsWithLongTitle(),
                'showSearch' => false,
                'emptyOption' => '-- No Division --'
            ]
            ]);
    }

    public function onRelationButtonApprove()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds)) {
            $match = MatchModel::findOrFail(post('model_id'));
            $checkedIds = array_unique($checkedIds);
            foreach ($checkedIds as $casterId) {
                $match->casters()->updateExistingPivot($casterId, ['approved' => 1]);
            }
        }
        $this->initForm($match);
        $this->initRelation($match, 'casters');
        return [
            '#castersPartial' => $this->relationRender('casters', ['readOnly' => false])
        ];
    }

    public function onRelationButtonParseReplay()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds)) {
            $checkedIds = array_unique($checkedIds);
            foreach ($checkedIds as $gameId) {
                $game = Game::findOrFail($gameId);
                $game->parseReplay();
            }
        }
    }

    public function onTimelineCreation()
    {
        $match = MatchModel::findOrFail(post('model_id'));
        $timeline = new Timeline();
        $timeline->type = 'Invalid.Type';
        $timeline->save();
        $timeline->matches()->add($match);
        $this->initForm($match);
        $this->initRelation($match, 'timeline');
        return [
            '#timelinePartial' => $this->relationRender('timeline', ['readOnly' => false])
        ];
    }
}
