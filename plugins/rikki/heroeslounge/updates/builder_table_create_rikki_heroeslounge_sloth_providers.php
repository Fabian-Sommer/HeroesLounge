<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateRikkiHeroesloungeSlothProviders extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_sloth_providers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('provider_id');
            $table->string('provider_token');
            $table->index(['provider_id', 'provider_token'], 'provider_id_token_index');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_sloth_providers');
    }
}