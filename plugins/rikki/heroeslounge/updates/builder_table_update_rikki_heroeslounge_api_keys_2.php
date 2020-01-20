<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeApiKeys2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_api_keys', function($table)
        {
            $table->bigInteger('total_used')->unsigned()->default(0);
            $table->integer('limit')->default(1)->change();
            $table->integer('seconds_duration')->default(1)->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_api_keys', function($table)
        {
            $table->dropColumn('total_used');
            $table->integer('limit')->default(null)->change();
            $table->integer('seconds_duration')->default(null)->change();
        });
    }
}
