<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGames3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->integer('map_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->dropColumn('map_id');
        });
    }
}