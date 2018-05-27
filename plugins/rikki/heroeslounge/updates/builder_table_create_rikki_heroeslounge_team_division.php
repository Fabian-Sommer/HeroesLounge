<?php namespace Rikki\Heroeslounge\Updates;
 
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeTeamDivision extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_team_division', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->integer('div_id')->unsigned();
            $table->integer('win_count')->unsigned();
            $table->integer('match_count')->unsigned();
            $table->boolean('bye');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_team_division');
    }
}
