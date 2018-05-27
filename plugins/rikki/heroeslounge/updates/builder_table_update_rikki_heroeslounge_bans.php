<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeBans extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_bans', function($table)
        {
            $table->integer('hero_id')->nullable()->change();
            $table->integer('talent_id')->nullable()->change();
            $table->integer('season_id')->nullable()->change();
            $table->integer('round_start')->nullable()->change();
            $table->integer('round_length')->nullable()->change();
            $table->text('literal')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_bans', function($table)
        {
            $table->integer('hero_id')->nullable(false)->change();
            $table->integer('talent_id')->nullable(false)->change();
            $table->integer('season_id')->nullable(false)->change();
            $table->integer('round_start')->nullable(false)->change();
            $table->integer('round_length')->nullable(false)->change();
            $table->text('literal')->nullable(false)->change();
        });
    }
}
