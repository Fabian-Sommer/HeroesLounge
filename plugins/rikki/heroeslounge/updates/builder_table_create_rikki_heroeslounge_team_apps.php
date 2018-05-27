<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeTeamApps extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_team_apps', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('team_id');
            $table->integer('user_id');
            $table->boolean('approved')->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_team_apps');
    }
}
