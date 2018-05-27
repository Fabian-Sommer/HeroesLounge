<?php namespace Rikki\Heroeslounge\Updates;
 
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeCountry extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_country', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_country');
    }
}
