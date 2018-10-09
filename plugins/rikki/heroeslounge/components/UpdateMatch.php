<?php

namespace Rikki\Heroeslounge\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Game;
use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\classes\ReplayParsing\ReplayParsing;
use Auth;
use Input;
use Redirect;
use Validator;
use Flash;

class UpdateMatch extends ComponentBase
{
    public $match = null;
    public $user = null;
    public $maps = null;
    public $opp = null;
    public $winner = null;
    public $t = null;
    public function componentDetails()
    {
        return [
            'name'        => 'Update Match',
            'description' => 'Allows Captains to save their matches'
        ];
    }


    public function onRender()
    {
        $this->user = Auth::getUser();
        if (!$this->user) {
            Flash::error('You are not authenticated!');
            return Redirect::to('/');
        }

        $this->addJs('/plugins/rikki/heroeslounge/assets/js/selectFile.js');
        $this->match = Match::find($this->property('id'));
        if ($this->match) {
            $this->maps = Map::where('enabled', true)->whereNotIn('id', $this->match->games()->lists('map_id'))->get();
            if ($this->match->teams->count() == 2) {
                $teamids = [$this->match->teams[0]->id, $this->match->teams[1]->id];
            } else {
                $this->match = null;
                return;
            }
            
        
    
            $this->opp = $this->match->teams()->where('team_id', '!=', $this->user->sloth->team_id)->first();
            $tc1 = $this->match->games->where('winner_id', $teamids[0])->count();
            $tc2 = $this->match->games->where('winner_id', $teamids[1])->count();
            if ($tc1 != $tc2) {
                $this->winner =  $tc1 > $tc2 ? $this->match->teams[0] : $this->match->teams[1];
            }
        }
    }

    public function onMyRender()
    {
        $timezoneoffset = (int)$_POST['time'];
        $timezoneName = $_POST['timezone'];
        
        $this->match = Match::find($_POST['match_id']);

        if (!in_array($timezoneName, timezone_identifiers_list())) {
            $timezoneName = "Europe/Berlin";
        }

        $containerId = "#deadline".$this->match->id;
        return [
            $containerId => $this->renderPartial('@deadline', ['timezone' => $timezoneName, 'match' => $this->match])
        ];
        
    }


    public function onGameSave()
    {
        $error = false;
        $r = Input::file('replay');
        if ($r == null) {
            Flash::error('A replay file is required.');
            $error = true;
        } elseif (strtolower($r->getClientOriginalExtension()) != 'stormreplay') {
            Flash::error('A replay file has to be a StormReplay file.');
            $error = true;
        }
        if ($error) {
            return Redirect::refresh();
        }

        $replay = new \System\Models\File;
        $replay->fromPost($r);
        $replay->save();
        
        $replayParser = new ReplayParsing;
        $replayParser->match = Match::find(post('match'));
        $replayParser->replay = $replay;
        $replayParser->uploadingTeam = Auth::getUser()->sloth->team;

        $validationResult = $replayParser->validateResult();
        if ($validationResult[0]) {
            $replay->delete();
            Flash::error($validationResult[1]);
            return Redirect::refresh();
        }



        $game = new Game;
        $game->match_id = post('match');
        $game->save();
        //The new game needs to be saved first, otherwise the Files do not have a Game id to save.
        $game->replay()->add($replay);
        $game->save();

        $replayParser->game = $game;
        $replayParser->saveResult();
        $replayParser->addGameDuration();
        $replayParser->addTrackerDetails();

        Flash::success($this->match.' The game has been saved!');
        return Redirect::refresh();
    }

    public function onMatchSave()
    {
        $match = Match::findOrFail(post('match_id'));
        $match->determineWinnerAndSave();
        Flash::success('The match has been saved!');
        return Redirect::refresh();
    }

    public function onGameDelete()
    {
        Game::destroy(post('game'));
        return Redirect::refresh();
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
