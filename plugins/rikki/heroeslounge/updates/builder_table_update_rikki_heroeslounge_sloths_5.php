<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSloths5 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->date('birthday')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->date('birthday')->nullable(false)->change();
        });
    }
}
