<?php namespace Rikki\Heroeslounge\Updates;

use Db;
use October\Rain\Database\Updates\Migration;
use Rikki\Heroeslounge\Models\Map;
class MapEntriesDefault extends Migration
{
    public function up()
    {
        Db::table('rikki_heroeslounge_maps')->insert([
            ['title' => 'Haunted Mines'],
            ['title' => 'Towers of Doom'],
            ['title' => 'Infernal Shrines'],
            ['title' => 'Battlefield of Eternity'],
            ['title' => 'Tomb of the Spider Queen'],
            ['title' => 'Sky Temple'],
            ['title' => 'Garden of Terror'],
            ['title' => 'Blackheart\'s Bay'],
            ['title' => 'Dragon Shire'],
            ['title' => 'Cursed Hollow'],
            ['title' => 'Braxis Holdout'],
            ['title' => 'Warhead Junction']
        ]);
        
        
    }
    
    public function down()
    {
       Map::truncate();
    }
}