<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeHeroes extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_heroes', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('title', 255)->nullable(false)->unsigned(false)->default(null)->change();
            $table->text('role')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_heroes', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
            $table->text('title')->nullable(false)->unsigned(false)->default(null)->change();
            $table->text('role')->nullable(false)->change();
        });
    }
}