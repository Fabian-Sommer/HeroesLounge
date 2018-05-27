<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSeasonFreeagent extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_season_freeagent', function($table)
        {
            $table->renameColumn('user_id', 'sloth_id');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_season_freeagent', function($table)
        {
            $table->renameColumn('sloth_id', 'user_id');
        });
    }
}
