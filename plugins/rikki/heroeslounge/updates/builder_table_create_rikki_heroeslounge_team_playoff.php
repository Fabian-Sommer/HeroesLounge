<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeTeamPlayoff extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_team_playoff', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->integer('playoff_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('seed')->unsigned()->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_team_playoff');
    }
}
