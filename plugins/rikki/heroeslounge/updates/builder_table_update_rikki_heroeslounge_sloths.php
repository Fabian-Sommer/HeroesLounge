<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSloths extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->boolean('is_captain')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->dropColumn('is_captain');
        });
    }
}
