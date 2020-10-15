<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMaps3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_maps', function($table)
        {
            $table->text('translations');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_maps', function($table)
        {
            $table->dropColumn('translations');
        });
    }
}
