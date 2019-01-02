<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungePlayoffTeam extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_playoff_team', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('playoff_id')->unsigned();
            $table->integer('team_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_playoff_team');
    }
}
