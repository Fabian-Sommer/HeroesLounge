<?php namespace Rikki\Heroeslounge\Updates;
 
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeMatch extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_match', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->dateTime('wbp')->nullable();
            $table->integer('div_id')->unsigned();
            $table->integer('round')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_match');
    }
}
