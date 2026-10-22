<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteRikkiHeroesloungeCountry extends Migration
{
    public function up()
    {
        Schema::dropIfExists('rikki_heroeslounge_country');
    

    }
    
    public function down()
    {
          Schema::create('rikki_heroeslounge_country', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title', 255);
        });

        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->dropColumn('country_id');
            $table->dropColumn('state_id');
        });

     
    }
}