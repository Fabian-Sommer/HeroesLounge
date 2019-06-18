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

    public function afterDelete()
    {
        /*
            I was hoping this would fire when a team gets disbanded, but it doesn't.
        */
    }

    public function afterSave()
    {
        if ($this->isDirty('is_captain')) {
            $team = Team::find($this->team_id);
            $sloth = Sloth::find($this->sloth_id);

            if ($sloth->isCaptainOfTeam($team)) {
                $sloth->addDiscordCaptainRole();
            } else if (!$sloth->isCaptain()) {
                $sloth->removeDiscordCaptainRole();
            }
        }
    }

}
