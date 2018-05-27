<?php namespace Rikki\LoungeStatistics\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiLoungestatisticsSlothHeroStatistics extends Migration
{
    public function up()
    {
        Schema::table('rikki_loungestatistics_sloth_hero_statistics', function($table)
        {
            $table->renameColumn('total_xp_contribution', 'total_xp_contrib');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_loungestatistics_sloth_hero_statistics', function($table)
        {
            $table->renameColumn('total_xp_contrib', 'total_xp_contribution');
        });
    }
}
