<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGames2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->increments('id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->dropColumn('id');
        });
    }
}
