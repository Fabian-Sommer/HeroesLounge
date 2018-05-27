<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeTeams3 extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->boolean('disbanded')->default(0);
            $table->string('short_description', 255)->nullable()->unsigned(false)->default(null)->change();
            $table->dropColumn('is_active');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_teams', function($table)
        {
            $table->dropColumn('disbanded');
            $table->text('short_description')->nullable()->unsigned(false)->default(null)->change();
            $table->boolean('is_active');
        });
    }
}
