<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Db;

class AddRoles1 extends Migration
{
    public function up()
    {
       Db::table('rikki_heroeslounge_sloth_roles')->insert([
            ['title' => 'None'],
            ['title' => 'Ranged Flex']
        ]);
    }
    
    public function down()
    {
   
    }
}