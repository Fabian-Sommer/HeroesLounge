<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeApiKeys3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_api_keys', function($table)
        {
            $table->integer('has_write_access')->unsigned()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_api_keys', function($table)
        {
            $table->dropColumn('has_write_access');
        });
    }
}
