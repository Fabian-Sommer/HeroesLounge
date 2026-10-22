<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSeasons4 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_seasons', function($table)
        {
            $table->smallInteger('type')->unsigned()->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_seasons', function($table)
        {
            $table->dropColumn('type');
        });
    }
}
