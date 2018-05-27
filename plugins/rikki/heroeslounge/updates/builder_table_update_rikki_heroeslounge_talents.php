<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTalents extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_talents', function($table)
        {
            $table->integer('hero_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_talents', function($table)
        {
            $table->dropColumn('hero_id');
        });
    }
}