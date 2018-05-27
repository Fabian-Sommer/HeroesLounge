<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeHeroes extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_heroes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('title');
            $table->text('role');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_heroes');
    }
}
