<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeGameparticipationTalent extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_gameparticipation_talent', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('id')->unsigned();
            $table->integer('gameparticipation_id')->unsigned();
            $table->integer('talent_id')->unsigned();
            $table->integer('talent_tier')->unsigned();
            $table->primary(['id']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_gameparticipation_talent');
    }
}
