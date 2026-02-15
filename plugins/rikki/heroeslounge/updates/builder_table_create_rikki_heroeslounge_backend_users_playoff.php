<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeBackendUserPlayoff extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_backend_users_playoff', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('backend_user_id')->unsigned();
            $table->integer('playoff_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_backend_users_playoff');
    }
}
