<?php namespace Rikki\LoungeStatistics\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiLoungestatisticsSlothHeroStatistics extends Migration
{
    public function up()
    {
        Schema::create('rikki_loungestatistics_sloth_hero_statistics', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('sloth_id')->unsigned();
            $table->integer('hero_id')->unsigned();
            $table->integer('avg_kills')->unsigned();
            $table->integer('avg_assists');
            $table->integer('avg_deaths');
            $table->integer('total_kills');
            $table->integer('total_assists');
            $table->integer('total_deaths');
            $table->integer('avg_siege_dmg');
            $table->integer('avg_hero_dmg');
            $table->integer('avg_healing');
            $table->integer('avg_dmg_taken');
            $table->integer('avg_xp_contrib');
            $table->integer('total_siege_dmg');
            $table->integer('total_hero_dmg');
            $table->integer('total_healing');
            $table->integer('total_dmg_taken');
            $table->integer('total_xp_contribution');
            $table->integer('total_games');
            $table->integer('total_wins');
            $table->integer('total_losses');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_loungestatistics_sloth_hero_statistics');
    }
}
