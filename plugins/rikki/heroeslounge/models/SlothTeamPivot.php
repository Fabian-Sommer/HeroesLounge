<?php namespace Rikki\Heroeslounge\Models;

use October\Rain\Database\Pivot;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Team;
use Log;

class SlothTeamPivot extends Pivot
{
    public $timestamps = true;

    public $table = 'rikki_heroeslounge_sloth_team';

    public function afterSave()
    {
        if ($this->isDirty('is_captain')) {
            $sloth = Sloth::find($this->sloth_id);
            if ($this->is_captain) {
                $sloth->addDiscordCaptainRole();
            } else {
                if(!$this->isCaptainOfOtherTeam($sloth, $this->team_id)) {
                    $sloth->removeDiscordCaptainRole();
                }
            }
        }
    }

    public function isCaptainOfOtherTeam($sloth, $updatedTeamId) {
        return $sloth->teams->contains(function($team) use ($updatedTeamId) {
            return $team->id != $updatedTeamId && $team->pivot->is_captain;
        });
    }
}
