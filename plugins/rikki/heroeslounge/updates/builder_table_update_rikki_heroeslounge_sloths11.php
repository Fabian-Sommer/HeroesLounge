<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeSloths11 extends Migration
{
    public function up()

    {

        Schema::table('rikki_heroeslounge_sloths', function($table)

        {

            $table->string('timezone', 255);

        });

    }

    

    public function down()

    {

        Schema::table('rikki_heroeslounge_sloths', function($table)

        {

            $table->dropColumn('timezone');

        });

    }
}