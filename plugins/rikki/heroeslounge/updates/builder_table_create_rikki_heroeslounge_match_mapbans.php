<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeMatchMapbans extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_match_mapbans', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('match_id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->integer('map_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_match_mapbans');
    }
}
