<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSeasons extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_seasons', function($table)
        {
            $table->boolean('reg_open');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_seasons', function($table)
        {
            $table->dropColumn('reg_open');
        });
    }
}