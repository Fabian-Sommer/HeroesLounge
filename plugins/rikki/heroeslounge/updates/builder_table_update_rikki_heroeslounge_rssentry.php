<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeRssentry extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_rssentry', function($table)
        {
            $table->string('media', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_rssentry', function($table)
        {
            $table->dropColumn('media');
        });
    }
}
