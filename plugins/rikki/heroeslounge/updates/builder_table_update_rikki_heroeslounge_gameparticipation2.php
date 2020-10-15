<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeGameparticipation2 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_gameparticipation', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }

    public function down()
    {
        Schema::table('rikki_heroeslounge_gameparticipation', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
}
