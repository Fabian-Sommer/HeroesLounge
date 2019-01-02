<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteRikkiHeroesloungePlayoffTeam extends Migration
{
    public function up()
    {
        Schema::dropIfExists('rikki_heroeslounge_playoff_team');
    }
    
    public function down()
    {
        Schema::create('rikki_heroeslounge_playoff_team', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('playoff_id')->unsigned();
            $table->integer('team_id')->unsigned();
        });
    }
}
