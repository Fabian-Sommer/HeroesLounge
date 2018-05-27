<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMatch2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->boolean('is_played');
            $table->integer('winner_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->dropColumn('winner_id');
            $table->dropColumn('is_played');
        });
    }
}