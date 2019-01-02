<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungePlayoffs5 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->smallInteger('region_id')->unsigned()->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->dropColumn('region_id');
        });
    }
}
