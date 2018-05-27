<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeTimelineRelation extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_timeline_relation', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('timeline_id')->unsigned();
            $table->string('related_model_name', 255);
            $table->integer('related_model_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_timeline_relation');
    }
}
