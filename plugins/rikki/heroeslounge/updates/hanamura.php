<?php namespace Rikki\Heroeslounge\Updates;

use Db;
use October\Rain\Database\Updates\Migration;
class Hanamura extends Migration
{
    public function up()
    {
        Db::table('rikki_heroeslounge_maps')->insert([
            ['title' => 'Hanamura']
        ]);
        
        
    }
    
    public function down()
    {
    }
}