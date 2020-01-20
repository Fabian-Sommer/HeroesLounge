<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeApiKeys extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_api_keys', function($table)
        {
            $table->string('key', 50);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_api_keys', function($table)
        {
            $table->dropColumn('key');
        });
    }
}
