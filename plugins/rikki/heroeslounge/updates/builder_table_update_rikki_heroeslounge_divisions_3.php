<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeDivisions3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->integer('mmr_bound')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->dropColumn('mmr_bound');
        });
    }
}
