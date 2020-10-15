<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGames7 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->dropColumn('draft_screenshot_id');
            $table->dropColumn('replay_id');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->integer('draft_screenshot_id')->unsigned();
            $table->integer('replay_id')->unsigned();
        });
    }
}