<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeTalents extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_talents', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title', 255);
            $table->string('replay_title', 255)->nullable();
            $table->string('suspected_replay_title', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('image_url', 255);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_talents');
    }
}