<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeSlothArchive extends Migration
{
    public function up()
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
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_sloth_archive');
    }
}
