<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeamDivision2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_team_division', function($table)
        {
            $table->boolean('active')->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_team_division', function($table)
        {
            $table->dropColumn('active');
        });
    }
}