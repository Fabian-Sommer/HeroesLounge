<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeams2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->boolean('accepting_apps')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->dropColumn('accepting_apps');
        });
    }
}
