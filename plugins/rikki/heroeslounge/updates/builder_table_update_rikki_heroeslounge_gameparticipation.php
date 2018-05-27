<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGameparticipation extends Migration
{
    public function up()
    {
    	

        Schema::table('rikki_heroeslounge_gameparticipation', function($table)
        {
            $table->integer('team_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_gameparticipation', function($table)
        {
            $table->dropColumn('team_id');
        });
    }
}