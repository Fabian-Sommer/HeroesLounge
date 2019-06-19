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
            $noLongerCaptain = false;

            if ($sloth->teams->count() <= 1) {
                $noLongerCaptain = true;
            } else {
                $isCaptain = $sloth->teams->first(function($index, $team) {
                    return $team->id != $this->team_id && $team->pivot->is_captain;
                });

                if ($isCaptain != null) {
                    $noLongerCaptain = true;
                }
            }

            if ($this->is_captain) {
                $sloth->addDiscordCaptainRole();
            } else if (!$this->is_captain && $noLongerCaptain) {
                $sloth->removeDiscordCaptainRole();
            }
        }
    }

}
