<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSloths17 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->integer('heroesprofile_id')->nullable()->unsigned();
            $table->integer('heroesprofile_mmr');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->dropColumn('heroesprofile_id');
            $table->dropColumn('heroesprofile_mmr');
        });
    }
}
