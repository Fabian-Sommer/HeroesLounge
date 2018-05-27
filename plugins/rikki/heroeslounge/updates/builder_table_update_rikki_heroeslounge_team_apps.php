<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeamApps extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_team_apps', function($table)
        {
            $table->text('message');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_team_apps', function($table)
        {
            $table->dropColumn('message');
        });
    }
}
