<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungePlayoffs2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->integer('season_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->dropColumn('season_id');
        });
    }
}
