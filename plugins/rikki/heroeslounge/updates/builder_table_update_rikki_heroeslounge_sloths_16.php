<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSloths16 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->dropColumn('team_id');
            $table->dropColumn('is_captain');
            $table->dropColumn('divs_team_id');
            $table->dropColumn('is_divs_captain');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->integer('team_id')->unsigned();
            $table->boolean('is_captain')->default(0);
            $table->integer('divs_team_id')->unsigned();
            $table->boolean('is_divs_captain')->default(0);
        });
    }
}
