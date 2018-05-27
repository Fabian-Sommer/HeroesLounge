<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeGame extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_game', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('match_id')->unsigned();
            $table->integer('draft_screenshot_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('replay_id')->unsigned();
            $table->integer('winner_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_game');
    }
}
