<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeDivisions3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->string('overview_display_title', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->dropColumn('overview_display_title');
        });
    }
}
