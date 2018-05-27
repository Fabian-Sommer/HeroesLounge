<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTimeline4 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_timeline', function($table)
        {
            $table->dropColumn('team_id');
            $table->dropColumn('sloth_id');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_timeline', function($table)
        {
            $table->integer('team_id')->nullable()->unsigned();
            $table->integer('sloth_id')->nullable()->unsigned();
        });
    }
}
