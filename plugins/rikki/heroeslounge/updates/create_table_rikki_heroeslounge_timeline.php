<?php namespace Rikki\Heroeslounge\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTableRikkiHeroesloungeTimeline extends Migration
{
	public function up()
	{
		Schema::create('rikki_heroeslounge_timeline', function($table)
		{
			$table->engine = 'InnoDB';
			$table->increments('id')->unsigned();
			$table->integer('timelineable_id')->unsigned();
			$table->string('timelineable_type');
			$table->string('message');
		});
	}

	public function down()
	{
		Schema::dropIfExists('rikki_heroeslounge_timeline');
	}
}
