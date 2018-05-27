<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeDivisions2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->integer('playoff_id')->nullable()->unsigned();
            $table->integer('season_id')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->dropColumn('playoff_id');
            $table->integer('season_id')->nullable(false)->change();
        });
    }
}