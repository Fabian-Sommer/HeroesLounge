<?php namespace Rikki\Heroeslounge\Updates;
 
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeDivisions extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->string('title');
            $table->string('slug');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->dropColumn('title');
            $table->dropColumn('slug');
        });
    }
}
