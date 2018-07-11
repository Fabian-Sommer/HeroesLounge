<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGames6 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->integer('team_one_ban_three_id')->nullable()->unsigned();
            $table->integer('team_two_ban_three_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_games', function($table)
        {
            $table->dropColumn('team_one_ban_three_id');
            $table->dropColumn('team_two_ban_three_id');
        });
    }
}
