<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeMatchChannel extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_match_channel', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('match_id')->unsigned();
            $table->integer('channel_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_match_channel');
    }
}
