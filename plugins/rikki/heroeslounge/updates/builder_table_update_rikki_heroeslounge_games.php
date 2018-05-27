<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGames extends Migration
{
    public function up()
    {
        Schema::rename('rikki_heroeslounge_game', 'rikki_heroeslounge_games');
    }
    
    public function down()
    {
        Schema::rename('rikki_heroeslounge_games', 'rikki_heroeslounge_game');
    }
}
