<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeHeroes3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_heroes', function($table)
        {
            $table->text('translations');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_heroes', function($table)
        {
            $table->dropColumn('translations');
        });
    }
}
