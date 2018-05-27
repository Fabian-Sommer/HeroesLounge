<?php namespace Rikki\Heroeslounge\Controllers;

 
use Backend\Classes\Controller;
use BackendMenu;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Twitchchannel;
use Rikki\Heroeslounge\Models\Sloth;

class Casterscheduling extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\RelationController'];
    public $requiredPermissions = ['rikki.heroeslounge.casterscheduling'];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $channels = '';
    
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Rikki.Heroeslounge', 'manage-general', 'manage-schedules');
        $this->channels = Twitchchannel::where('id', '>=', '1')->get();
    }

    public function listExtendQuery($query)
    {
        $query->whereNotNull('wbp');
    }

    public function getChannels()
    {
        return $this->channels;
    }

    public function onChannelAssignment()
    {
        $match = Match::findOrFail(post('match_id'));
        if (post('channel_id') == 0) {
            $match->channel()->dissociate();
        } else {
            $channel = Twitchchannel::findOrFail(post('channel_id'));
            $match->channel = $channel;
        }
        $match->save();
    }

    public function onRequestApproval()
    {
        $match = Match::findOrFail(post('match_id'));
        $caster = $match->casters()->findOrFail(post('caster_id'));
        $caster->pivot->approved = 1;
        $caster->pivot->save();
        $returnDiv = '#castersMatch' . $match->id;
        return [
            $returnDiv => $this->makePartial('~/plugins/rikki/heroeslounge/models/match/casterscheduling/_casters_content.htm', ['record' => $match])
        ];
    }

    public function onRelationButtonApprove()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds)) {
            $match = Match::findOrFail(post('model_id'));
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

    public function onRequestDenial()
    {
        $match = Match::findOrFail(post('match_id'));
        $caster = $match->casters()->findOrFail(post('caster_id'));
        $caster->pivot->approved = 2;
        $caster->pivot->save();
        $returnDiv = '#castersMatch' . $match->id;
        return [
            $returnDiv => $this->makePartial('~/plugins/rikki/heroeslounge/models/match/casterscheduling/_casters_content.htm', ['record' => $match])
        ];
    }

    public function onRelationButtonDeny()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds)) {
            $match = Match::findOrFail(post('model_id'));
            foreach ($checkedIds as $casterId) {
                $match->casters()->updateExistingPivot($casterId, ['approved' => 2]);
            }
        }
        $this->initForm($match);
        $this->initRelation($match, 'casters');
        return [
            '#castersPartial' => $this->relationRender('casters', ['readOnly' => false])
        ];
    }
}
