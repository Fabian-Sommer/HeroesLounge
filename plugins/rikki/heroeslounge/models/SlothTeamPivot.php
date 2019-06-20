<?php namespace Rikki\Heroeslounge\Models;

use October\Rain\Database\Pivot;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Team;

/**
 * Sloth-Team Pivot Model
 */
class SlothTeamPivot extends Pivot
{
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_sloth_team';

    public function afterSave()
    {
        if ($this->isDirty('is_captain')) {
            $sloth = Sloth::find($this->sloth_id);            

            if ($this->is_captain) {
                $sloth->addDiscordCaptainRole();
            } else {
                if(!isCaptainOfOtherTeam ($sloth, $this->team_id)) {
                    $sloth->removeDiscordCaptainRole();
                }
            }
        }
    }

    public function isCaptainOfOtherTeam ($sloth, $updatedTeamId) {
        $teamsCaptainOf = $sloth->teams->filter(function($index, $team) {
            return $team->id != $updatedTeamId && $team->pivot->is_captain;
        });

        return $teamsCaptainOf->count() >= 1;
    }

}
