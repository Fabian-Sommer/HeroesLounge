<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeRegions extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_regions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title', 255);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_regions');
    }
}
