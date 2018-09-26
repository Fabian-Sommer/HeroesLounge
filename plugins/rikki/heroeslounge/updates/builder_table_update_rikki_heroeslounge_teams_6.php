<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeams6 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->string('server_preference', 255);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->dropColumn('server_preference');
        });
    }
}
