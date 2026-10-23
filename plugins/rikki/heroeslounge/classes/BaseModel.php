<?php namespace Rikki\Heroeslounge\Classes;

use Winter\Storm\Database\Model;

/**
 * Base class for the Heroeslounge models.
 */
abstract class BaseModel extends Model
{
    /**
     * @var array<string, string[]> Relation methods found per model class.
     */
    protected static array $cachedRelationMethods = [];

    /**
     * winter/storm 1.2 only remembers its reflection scan for relation methods when a
     * class has such methods. Our models define relations as arrays only, so the scan
     * would run again on every relation lookup (see wintercms/storm#250). Cache the
     * result per class. Instances with extension or dynamic methods are not cached,
     * because those can add relation methods to a single instance.
     */
    public function getRelationMethods(bool $ignoreResolved = false): array
    {
        if ($ignoreResolved || !empty($this->extensionData['methods']) || !empty($this->extensionData['dynamicMethods'])) {
            return parent::getRelationMethods($ignoreResolved);
        }

        return static::$cachedRelationMethods[static::class] ??= parent::getRelationMethods();
    }
}
