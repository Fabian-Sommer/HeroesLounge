<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeSubstitutes extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_substitutes', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('sub_id')->unsigned();
            $table->integer('registrant_id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->integer('match_id')->unsigned();
            $table->boolean('approved')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_substitutes');
    }
}
