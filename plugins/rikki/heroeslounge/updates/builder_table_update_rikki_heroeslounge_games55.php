<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGames55 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->time('duration')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->dropColumn('duration');
        });
    }
}