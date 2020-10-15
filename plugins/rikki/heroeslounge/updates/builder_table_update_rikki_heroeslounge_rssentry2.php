<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeRssentry2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_rssentry', function($table)
        {
            $table->dateTime('publication_date');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_rssentry', function($table)
        {
            $table->dropColumn('publication_date');
        });
    }
}
