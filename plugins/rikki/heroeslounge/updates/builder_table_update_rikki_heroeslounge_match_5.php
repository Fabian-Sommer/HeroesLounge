<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMatch5 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->string('yt_playlist')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->dropColumn('yt_playlist');
        });
    }
}
