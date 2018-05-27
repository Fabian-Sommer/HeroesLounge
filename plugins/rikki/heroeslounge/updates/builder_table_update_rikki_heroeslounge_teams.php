<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeams extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->string('facebook_url')->nullable();
            $table->string('twitch_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('website_url')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->dropColumn('facebook_url');
            $table->dropColumn('twitch_url');
            $table->dropColumn('twitter_url');
            $table->dropColumn('youtube_url');
            $table->dropColumn('website_url');
        });
    }
}
