<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSeasons2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_seasons', function($table)
        {
            $table->boolean('mm_active');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_seasons', function($table)
        {
            $table->dropColumn('mm_active');
        });
    }
}
