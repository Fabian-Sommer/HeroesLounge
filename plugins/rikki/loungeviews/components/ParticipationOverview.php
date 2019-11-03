<?php namespace Rikki\LoungeViews\Components;


use Cms\Classes\ComponentBase;
use Auth;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\classes\Discord;
use Log;
use Redirect;
use Flash;

class ParticipationOverview extends ComponentBase
{
    
    public $user = null;
    public $userCaptainedTeams = null;
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
        if ($this->user && $this->season) {
            $this->userCaptainedTeams = $this->user->sloth->getCaptainedTeams();
            foreach ($this->user->sloth->teams as $key => $team) {
                if ($this->season->teams->contains($team)) {
                    $this->signedUp = true;
                }
            }
            if ($this->season->free_agents->contains($this->user->sloth)) {
                $this->signedUp = true;
            }
        }
        if ($this->season) {
            $this->page->title = $this->season->title.' Participation';
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
        $this->user = Auth::getUser();
        $this->season = Seasons::find($_POST['season_id']);
        if ($this->user != null) {
            $team = $this->user->sloth->teams->where('id', $_POST['team_id'])->first();
            if ($team != null && $team->pivot->is_captain && $team->region_id == $this->season->region_id && !$this->season->teams->contains($team)) {
                $eligible = $team->isEligibleForSeason($this->season);
                if ($eligible === true) {
                    $this->season->teams()->add($team);
                    Flash::success('Your team is now signed up for '.$this->season->title);

                    foreach ($team->sloths as $sloth) {
                        if ($this->season->free_agents->contains($sloth)) {
                            $this->season->free_agents()->remove($sloth);
                            Discord\RoleManagement::UpdateUserRole("DELETE", $sloth->discord_id, "FreeAgent");
                        }
                    }

                    return Redirect::refresh();
                } else {
                    //$eligible holds a sloth that is already participating with another team
                    Flash::error('A member of this team is already participating with another team: '.$eligible->title);
                    return Redirect::refresh();
                }
            }
        }
    }

    public function onSlothSignup()
    {
        $this->user = Auth::getUser();
        $this->season = Seasons::find($_POST['season_id']);
        if ($this->user != null) {
            $sloth = $this->user->sloth;
            if ($sloth != null && $sloth->region_id == $this->season->region_id && !$this->season->free_agents->contains($sloth)) {
                $this->season->free_agents()->add($sloth);
                Discord\RoleManagement::UpdateUserRole("PUT", $sloth->discord_id, "FreeAgent");
                Flash::success('You are now signed up for '.$this->season->title.' as a free agent.');
                return Redirect::refresh();
            }
        }
    }

    public function onSlothRemoveSignUp()
    {
        $this->user = Auth::getUser();
        $this->season = Seasons::find($_POST['season_id']);
        if ($this->user != null) {
            $sloth = $this->user->sloth;
            $this->season->free_agents()->remove($sloth);
            Discord\RoleManagement::UpdateUserRole("DELETE", $sloth->discord_id, "FreeAgent");
            Flash::error('You will not participate in '. $this->season->title. ' - Sad to see you go!');
            return Redirect::refresh();
        }
    }
}
