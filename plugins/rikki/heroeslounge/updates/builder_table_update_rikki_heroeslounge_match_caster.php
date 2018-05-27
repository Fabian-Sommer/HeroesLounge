<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeMatchCaster extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_match_caster', function($table)
        {
            $table->smallInteger('approved')->nullable(false)->unsigned()->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_match_caster', function($table)
        {
            $table->boolean('approved')->nullable(false)->unsigned(false)->default(0)->change();
        });
    }
}