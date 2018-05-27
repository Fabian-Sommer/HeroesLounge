<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeBans extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_bans', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('hero_id')->unsigned();
            $table->integer('talent_id')->unsigned();
            $table->integer('season_id')->unsigned();
            $table->integer('round_start')->unsigned();
            $table->integer('round_length')->unsigned();
            $table->text('literal');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_bans');
    }
}
