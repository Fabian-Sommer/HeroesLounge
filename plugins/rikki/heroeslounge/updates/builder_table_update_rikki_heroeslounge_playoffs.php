<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungePlayoffs extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->string('title', 255);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_playoffs', function($table)
        {
            $table->dropColumn('title');
        });
    }
}