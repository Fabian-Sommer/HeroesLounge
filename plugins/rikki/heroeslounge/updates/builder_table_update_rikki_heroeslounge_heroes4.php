<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeHeroes4 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_heroes', function($table)
        {
            $table->integer('masterleague_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_heroes', function($table)
        {
            $table->dropColumn('masterleague_id');
        });
    }
}