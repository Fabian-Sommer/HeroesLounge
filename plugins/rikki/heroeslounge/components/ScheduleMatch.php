<?php namespace Rikki\Heroeslounge\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Rikki\Heroeslounge\Models\Match;
use Input;
use Redirect;
use Flash;

class ScheduleMatch extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Schedule Match',
            'description' => 'Allows Captains to schedule a match'
        ];
    }

    public $match = null;
    public $opp = null;
    public $user = null;


    public function onRender()
    {
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/jquery.datetimepicker.css');
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/heroeslounge.css');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/jquery.datetimepicker.full.js');
        $this->user = Auth::getUser();
        $this->match = Match::find($this->property('id'));

        if ($this->match) {
            if ($this->match->teams[0]->id == $this->user->sloth->team_id) {
                $this->opp = $this->match->teams[1];
            } else {
                $this->opp = $this->match->teams[0];
            }
        }
    }



    public function onSaveDate()
    {
        $match = Match::find(post('match_id'));
        if ($match) {
            $date = post('date');
            if ($date != null) {
                try {
                    $match->wbp = date('Y-m-d H:i:s', strtotime($date));
                    if ($match->tbp != null && $match->wbp < $match->tbp) {
                        $match->save();
                        Flash::success('Match has been successfully scheduled for '.$date);
                    } else {
                        Flash::error('The match has to be played before ' . $match->tbp);
                    }
                    
                } catch (Exception $e) {
                    Flash::error($e->getMessage());
                } finally {
                    return Redirect::refresh();
                }
            } else {
                Flash::error('Please provide a date!');
            }
        }
    }



    public function defineProperties()
    {
        return [
            'id' => [
                'title' => 'MatchID',
                'description' => 'MatchID to grab data from',
                'type' => 'string',
                'required' => true,
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The MatchID property can contain only numeric symbols'
            ]
            ];
    }
}
