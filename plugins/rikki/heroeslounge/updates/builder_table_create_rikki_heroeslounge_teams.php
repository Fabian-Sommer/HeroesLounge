<?php namespace Rikki\Heroeslounge\Updates;
 
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeTeams extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_teams', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->string('slug');
            $table->boolean('is_active');
            $table->integer('slothrating')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_teams');
    }
}
