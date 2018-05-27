<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTimelineables extends Migration
{
    public function up()
    {
        Schema::rename('rikki_heroeslounge_timeline_relation', 'rikki_heroeslounge_timelineables');
        Schema::table('rikki_heroeslounge_timelineables', function($table)
        {
            $table->renameColumn('related_model_name', 'timelineable_type');
            $table->renameColumn('related_model_id', 'timelineable_id');
        });
    }
    
    public function down()
    {
        Schema::rename('rikki_heroeslounge_timelineables', 'rikki_heroeslounge_timeline_relation');
        Schema::table('rikki_heroeslounge_timeline_relation', function($table)
        {
            $table->renameColumn('timelineable_type', 'related_model_name');
            $table->renameColumn('timelineable_id', 'related_model_id');
        });
    }
}