<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeMaps extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_maps', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title', 255);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_maps');
    }
}
