<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeamAvailability extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_team_availability', function($table)
        {
            $table->smallInteger('day')->nullable();
            $table->dropColumn('date');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_team_availability', function($table)
        {
            $table->dropColumn('day');
            $table->date('date')->nullable();
        });
    }
}
