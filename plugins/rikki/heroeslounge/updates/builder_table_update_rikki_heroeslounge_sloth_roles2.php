<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Db;

class BuilderTableUpdateRikkiHeroesloungeSlothRoles2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_sloth_roles', function($table)
        {
            $table->increments('id')->unsigned(false)->change();
        });
        Db::table('rikki_heroeslounge_sloth_roles')->where('title','None')->update(['id'=> -1]);
    }
    
    public function down()
    {
        Db::table('rikki_heroeslounge_sloth_roles')->where('title','None')->update(['id'=> 0]);
        Schema::table('rikki_heroeslounge_sloth_roles', function($table)
        {
            $table->increments('id')->unsigned()->change();
        });
    }
}