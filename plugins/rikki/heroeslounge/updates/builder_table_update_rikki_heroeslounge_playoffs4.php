<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungePlayoffs4 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->boolean('reg_open');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->dropColumn('reg_open');
        });
    }
}
