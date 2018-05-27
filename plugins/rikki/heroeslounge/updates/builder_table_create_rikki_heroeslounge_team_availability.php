<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeTeamAvailability extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_team_availability', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('team_id');
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_team_availability');
    }
}
