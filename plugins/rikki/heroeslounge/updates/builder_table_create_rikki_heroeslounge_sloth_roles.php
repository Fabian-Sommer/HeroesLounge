<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Db;
class BuilderTableCreateRikkiHeroesloungeSlothRoles extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_sloth_roles', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('role');
        });

    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_sloth_roles');
    }
}