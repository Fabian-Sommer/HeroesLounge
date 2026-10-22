<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGameparticipation3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_gameparticipation', function($table)
        {
            $table->integer('draft_order')->nullable()->unsigned();
            $table->integer('kills')->nullable()->unsigned();
            $table->integer('deaths')->nullable()->unsigned();
            $table->integer('assists')->nullable()->unsigned();
            $table->integer('experience_contribution')->nullable()->unsigned();
            $table->integer('healing')->nullable()->unsigned();
            $table->integer('siege_damage')->nullable()->unsigned();
            $table->integer('hero_damage')->nullable()->unsigned();
            $table->integer('damage_taken')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_gameparticipation', function($table)
        {
            $table->dropColumn('draft_order');
            $table->dropColumn('kills');
            $table->dropColumn('deaths');
            $table->dropColumn('assists');
            $table->dropColumn('experience_contribution');
            $table->dropColumn('healing');
            $table->dropColumn('siege_damage');
            $table->dropColumn('hero_damage');
            $table->dropColumn('damage_taken');
        });
    }
}
