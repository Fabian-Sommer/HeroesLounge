<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSloths4 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->string('role', 20)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->dropColumn('role');
        });
    }
}
