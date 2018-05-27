<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeamApps3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_team_apps', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_team_apps', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
}