<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTimeline2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_timeline', function($table)
        {
            $table->string('type', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_timeline', function($table)
        {
            $table->dropColumn('type');
        });
    }
}
