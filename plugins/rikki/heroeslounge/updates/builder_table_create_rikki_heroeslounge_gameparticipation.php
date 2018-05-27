<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeGameparticipation extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_gameparticipation', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title', 255);
            $table->integer('sloth_id')->nullable()->unsigned();
            $table->integer('hero_id')->nullable()->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('game_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_gameparticipation');
    }
}
