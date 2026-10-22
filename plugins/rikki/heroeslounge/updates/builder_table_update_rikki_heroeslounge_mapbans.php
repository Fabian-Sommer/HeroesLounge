<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMapbans extends Migration
{
    public function up()
    {
        Schema::rename('rikki_heroeslounge_match_mapbans', 'rikki_heroeslounge_mapbans');
    }
    
    public function down()
    {
        Schema::rename('rikki_heroeslounge_mapbans', 'rikki_heroeslounge_match_mapbans');
    }
}
