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

class BuilderTableCreateRikkiHeroesloungeTeamMatch extends Migration
{
    public function up()
    {
        Schema::create('rikki_heroeslounge_team_match', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->integer('match_id')->unsigned();
            $table->integer('team_score')->unsigned();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('rikki_heroeslounge_team_match');
    }
}
