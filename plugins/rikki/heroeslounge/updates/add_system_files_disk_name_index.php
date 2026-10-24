<?php namespace Rikki\Heroeslounge\Updates;

use Db;
use Schema;
use Winter\Storm\Database\Updates\Migration;

/**
 * Winter's resize filter looks files up by disk_name when it is given a URL, and
 * system_files has no index on that column, so every lookup scans the whole table.
 * Remove this if Winter adds such an index itself.
 */
class AddSystemFilesDiskNameIndex extends Migration
{
    const INDEX = 'system_files_disk_name_index';

    public function up()
    {
        if ($this->indexExists()) {
            return;
        }

        Schema::table('system_files', function ($table) {
            $table->index('disk_name', self::INDEX);
        });
    }

    public function down()
    {
        if (!$this->indexExists()) {
            return;
        }

        Schema::table('system_files', function ($table) {
            $table->dropIndex(self::INDEX);
        });
    }

    protected function indexExists(): bool
    {
        return Db::table('information_schema.statistics')
            ->where('table_schema', Db::getDatabaseName())
            ->where('table_name', Db::getTablePrefix() . 'system_files')
            ->where('index_name', self::INDEX)
            ->exists();
    }
}
