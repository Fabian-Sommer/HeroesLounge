<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeamDivision extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_team_division', function($table)
        {
            $table->integer('free_win_count')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_team_division', function($table)
        {
            $table->dropColumn('free_win_count');
        });
    }
}