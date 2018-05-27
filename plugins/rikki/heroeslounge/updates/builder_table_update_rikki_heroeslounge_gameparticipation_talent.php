<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGameparticipationTalent extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_gameparticipation_talent', function($table)
        {
            $table->increments('id')->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_gameparticipation_talent', function($table)
        {
            $table->integer('id')->change();
        });
    }
}