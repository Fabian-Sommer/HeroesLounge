<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteRikkiHeroesloungeSlothArchive extends Migration
{
    public function up()
    {
        Schema::dropIfExists('rikki_heroeslounge_sloth_archive');
    }
    
    public function down()
    {
        Schema::create('rikki_heroeslounge_sloth_archive', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('sloth_id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->timestamp('deleted_at')->nullable();
        });
    }
}
