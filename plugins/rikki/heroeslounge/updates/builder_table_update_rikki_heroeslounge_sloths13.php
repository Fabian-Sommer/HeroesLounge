<?php namespace Rikki\Heroeslounge\Updates;
use Schema;
use October\Rain\Database\Updates\Migration;
class BuilderTableUpdateRikkiHeroesloungeSloths13 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_sloths', function($table)
        {
            $table->string('discord_id');
        });
    }

    public function down()
    {
      Schema::table('rikki_heroeslounge_sloths', function($table)
      {
          $table->dropColumn('discord_id');
      });
    }
}
