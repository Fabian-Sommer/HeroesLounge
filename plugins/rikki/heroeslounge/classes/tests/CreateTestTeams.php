<?php namespace Rikki\Heroeslounge\classes\tests;

  
use Rikki\Heroeslounge\Models\Team;

class CreateTestTeams
{
    public function createTeams()
    {
        for ($i=0;$i<10;$i++) {
            $team = new Team;
            $team->title = "team ".$i;
            $team->is_active = true;
            $team->short_description = "Shortdescription team ".$i;
            $team->save();
        }
    }
}
