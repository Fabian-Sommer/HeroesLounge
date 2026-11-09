<?php namespace Rikki\Heroeslounge\Updates;

use Db;
use Schema;
use Winter\Storm\Database\Updates\Migration;

/**
 * The timeline tables had no indexes besides their primary keys, so every lookup
 * of a timeline entry's teams, matches, divisions or sloths scanned the whole
 * rikki_heroeslounge_timelineables table.
 */
class AddTimelineIndexes extends Migration
{
    const INDEXES = [
        'rikki_heroeslounge_timelineables' => [
            'timelineables_timeline_id_index' => ['timeline_id'],
            'timelineables_timelineable_index' => ['timelineable_type', 'timelineable_id'],
        ],
        'rikki_heroeslounge_timeline' => [
            'timeline_created_at_index' => ['created_at'],
        ],
    ];

    public function up()
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (!$this->indexExists($table, $name)) {
                    Schema::table($table, function ($table) use ($name, $columns) {
                        $table->index($columns, $name);
                    });
                }
            }
        }
    }

    public function down()
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if ($this->indexExists($table, $name)) {
                    Schema::table($table, function ($table) use ($name) {
                        $table->dropIndex($name);
                    });
                }
            }
        }
    }

    protected function indexExists(string $table, string $name): bool
    {
        return Db::table('information_schema.statistics')
            ->where('table_schema', Db::getDatabaseName())
            ->where('table_name', Db::getTablePrefix() . $table)
            ->where('index_name', $name)
            ->exists();
    }
}
