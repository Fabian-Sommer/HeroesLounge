<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Db;

class BuilderTableUpdateRikkiHeroesloungeSlothRoles extends Migration
{
    public function up()
    {
       Schema::table('rikki_heroeslounge_sloth_roles', function($table)
        {
            $table->renameColumn('role', 'title');
        });
        
        Db::table('rikki_heroeslounge_sloth_roles')->insert([
            ['title' => 'Tank'],
            ['title' => 'Support'],
            ['title' => 'Flex'],
            ['title' => 'Bruiser'],
            ['title' => 'Assassin']
        ]);
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_sloth_roles', function($table)
        {
            $table->renameColumn('title', 'role');
        });
    }
}