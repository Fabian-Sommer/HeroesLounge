<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMatch3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->integer('winner_id')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->integer('winner_id')->nullable(false)->change();
        });
    }
}