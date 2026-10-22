<?php namespace Rikki\Heroeslounge\Updates;
/*
 * Intended to be used along https://octobercms.com/
 * PHP Version 5
 * @category   Heroes Lounge Upgrades
 * @package    Rikki/Heroeslounge
 * @author     Alexander Braun kealsera@gmail.com
 * @copyright  Copyright (c) 2017 Alexander Braun
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    1.0
 * @link       https://github.com/kealsera/SlothLounge
 * @since      Class available since Release 1.0
 */
use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateRikkiHeroesloungeDivisions extends Migration
{
    public function up()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->string('title');
            $table->string('slug');
        });
    }
    
    public function down()
    {
        Schema::table('rikki_heroeslounge_divisions', function($table)
        {
            $table->dropColumn('title');
            $table->dropColumn('slug');
        });
    }
}
