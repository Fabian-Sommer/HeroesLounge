<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungePlayoffs6 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->string('slug', 255);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->dropColumn('slug');
        });
    }
}
