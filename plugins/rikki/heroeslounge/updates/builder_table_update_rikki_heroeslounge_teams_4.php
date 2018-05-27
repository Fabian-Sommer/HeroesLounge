<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeams4 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->string('facebook_url', 255)->nullable(false)->change();
            $table->string('twitch_url', 255)->nullable(false)->change();
            $table->string('twitter_url', 255)->nullable(false)->change();
            $table->string('youtube_url', 255)->nullable(false)->change();
            $table->string('website_url', 255)->nullable(false)->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->string('facebook_url', 255)->nullable()->change();
            $table->string('twitch_url', 255)->nullable()->change();
            $table->string('twitter_url', 255)->nullable()->change();
            $table->string('youtube_url', 255)->nullable()->change();
            $table->string('website_url', 255)->nullable()->change();
        });
    }
}
