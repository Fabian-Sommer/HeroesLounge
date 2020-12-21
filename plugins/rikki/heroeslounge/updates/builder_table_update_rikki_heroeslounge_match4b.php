<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMatch4b extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->integer('div_id')->unsigned()->change();
            $table->integer('round')->unsigned()->change();
            $table->dateTime('tbp')->nullable()->change();
            $table->dateTime('schedule_date')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->integer('div_id')->unsigned(false)->change();
            $table->integer('round')->unsigned(false)->change();
            $table->dateTime('tbp')->nullable(false)->change();
            $table->dateTime('schedule_date')->nullable(false)->change();
        });
    }
}
