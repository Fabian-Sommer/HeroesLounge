<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMatch6 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->dropColumn('channel_id');
            $table->dropColumn('yt_playlist');
        });
    }    

    public function down()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->integer('channel_id')->nullable()->unsigned();
            $table->string('yt_playlist', 255)->nullable();
        });
    }
}
