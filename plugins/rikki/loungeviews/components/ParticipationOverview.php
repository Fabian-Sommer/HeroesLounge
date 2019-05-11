<?php namespace Rikki\LoungeViews\Components;


use Cms\Classes\ComponentBase;
use Auth;
use Rikki\Heroeslounge\Models\Season as Seasons;

class ParticipationOverview extends ComponentBase
{
    
    public $user = null;
    public $season = null;
    public $signedUp = false;

    public function componentDetails()
    {
        return [
            'name'        => 'Participation Overview',
            'description' => 'Shows Teams & Free Agents for certain season'
        ];
    }



    public function onRender()
    {
        $this->user = Auth::getUser();
        $this->season = Seasons::find($this->property('id'));
        if ($this->season) {
            $this->page->title = $this->season->title.' Participation';
            if ($this->user && $this->user->sloth->team && $this->season->teams->contains($this->user->sloth->team)) {
                $signedUp = true;
            } else if ($this->user && $this->season->free_agents->contains($this->user->sloth)) {
                $signedUp = true;
            }
        }
    }

    public function defineProperties()
    {
        return [
            'id' => [
                'title' => 'Entity',
                'description' => 'The specific Season to take data from',
                'type' => 'dropdown',
                'placeholder' => 'Select specific Season'
            ]
        ];
    }

    public function getIdOptions()
    {
        $myData = Seasons::all();
        $retOptions = [];
        foreach ($myData as $entity) {
            $retOptions[$entity->id] = $entity->title;
        }
        return $retOptions;
    }

    public function onTeamSignup()
    {
        if ($this->user != null) {
            $team = $this->user->sloth->team;
            if ($team != null && $this->user->sloth->is_captain && $team->region_id == $this->region_id && !$this->season->teams->contains($team)) {
                $this->season->teams()->add($team);
                Flash::success('Your team is now signed up for '.$this->season->title);
                return Redirect::refresh();
            }
        }
    }

    public function onSlothSignup()
    {
        if ($this->user != null) {
            $sloth = $this->user->sloth;
            if ($sloth != null && $sloth->team != null) {
                Flash::error('You cannot be part of a team to sign up as a free agent!');
                return Redirect::refresh();
            }
            if ($sloth != null && $sloth->team == null && $sloth->region_id == $this->region_id && !$this->season->free_agents->contains($sloth)) {
                $this->season->free_agents()->add($sloth);
                Flash::success('You are now signed up for '.$this->season->title);
                return Redirect::refresh();
            }
        }
    }
}
