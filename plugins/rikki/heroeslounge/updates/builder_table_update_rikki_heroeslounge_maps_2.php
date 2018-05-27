<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMaps2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_maps', function($table)
        {
            $table->boolean('enabled')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_maps', function($table)
        {
            $table->dropColumn('enabled');
        });
    }
}
