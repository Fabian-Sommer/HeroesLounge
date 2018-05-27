<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteRikkiHeroesloungeGames extends Migration
{
    public function up()
    {
        Schema::dropIfExists('rikki_heroeslounge_games');
    }
    
    public function down()
    {
        Schema::create('rikki_heroeslounge_games', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('match_id')->unsigned();
        });
    }
}
