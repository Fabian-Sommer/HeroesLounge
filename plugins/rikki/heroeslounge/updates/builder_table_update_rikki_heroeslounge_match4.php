<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMatch4 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->dateTime('tbp');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->dropColumn('tbp');
        });
    }
}