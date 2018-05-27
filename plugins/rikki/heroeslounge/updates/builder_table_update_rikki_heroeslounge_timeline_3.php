<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTimeline3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_timeline', function($table)
        {
            $table->integer('team_id')->nullable()->unsigned();
            $table->integer('sloth_id')->nullable()->unsigned();
            $table->string('message', 255)->nullable()->change();
            $table->string('type', 255)->nullable(false)->change();
            $table->dropColumn('timelineable_id');
            $table->dropColumn('timelineable_type');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_timeline', function($table)
        {
            $table->dropColumn('team_id');
            $table->dropColumn('sloth_id');
            $table->string('message', 255)->nullable(false)->change();
            $table->string('type', 255)->nullable()->change();
            $table->integer('timelineable_id')->unsigned();
            $table->string('timelineable_type', 255);
        });
    }
}
