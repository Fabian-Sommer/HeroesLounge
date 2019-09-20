<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMatch7 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->timestamp('reschedule_date')->nullable();
            $table->integer('confirming_team_id');
        });
    }

    public function down()
    {
        Schema::table('rikki_heroeslounge_match', function($table)
        {
            $table->dropColumn('reschedule_date');
            $table->dropColumn('confirming_team_id');
        });
    }
}