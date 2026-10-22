<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeHeroes2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_heroes', function($table)
        {
            $table->string('image_url', 255);
            $table->string('attribute_name', 10);
            $table->dropColumn('role');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_heroes', function($table)
        {
            $table->dropColumn('image_url');
            $table->dropColumn('attribute_name');
            $table->text('role')->nullable();
        });
    }
}
