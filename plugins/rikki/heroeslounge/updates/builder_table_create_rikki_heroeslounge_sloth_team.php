<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeSlothTeam extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_sloth_team', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('sloth_id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->boolean('is_captain')->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_sloth_team');
    }
}
