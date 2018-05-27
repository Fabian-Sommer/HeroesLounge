<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeSeasonTeam extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_season_team', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->integer('season_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_season_team');
    }
}
