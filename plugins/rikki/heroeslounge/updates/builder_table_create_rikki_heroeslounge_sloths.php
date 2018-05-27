<?php namespace Rikki\Heroeslounge\Updates;
 
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeSloths extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_sloths', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('title',255);
            $table->string('battle_tag');
            $table->string('discord_tag');
            $table->date('birthday');
            $table->integer('country_id')->unsigned();
            $table->text('short_description')->nullable();
            $table->string('twitch_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->integer('user_id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_sloths');
    }
}
