<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMatch3b extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->integer('playoff_id')->nullable()->unsigned();
            $table->integer('playoff_position')->nullable()->unsigned();
            $table->integer('playoff_winner_next')->nullable()->unsigned();
            $table->integer('playoff_loser_next')->nullable()->unsigned();
            $table->integer('div_id')->nullable()->change();
            $table->integer('round')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->dropColumn('playoff_id');
            $table->dropColumn('playoff_position');
            $table->dropColumn('playoff_winner_next');
            $table->dropColumn('playoff_loser_next');
            $table->integer('div_id')->nullable(false)->change();
            $table->integer('round')->nullable(false)->change();
        });
    }
}
