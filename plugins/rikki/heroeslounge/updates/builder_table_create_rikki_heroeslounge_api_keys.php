<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeApiKeys extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_api_keys', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('limit')->unsigned();
            $table->integer('seconds_duration')->unsigned();
            $table->integer('used')->unsigned()->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_api_keys');
    }
}
