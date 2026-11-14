<?php namespace Rikki\Heroeslounge\Classes\Traits;

/**
 * Works around a bug in Winter 1.2.15's RelationController (introduced by
 * wintercms/winter#1554, not fixed upstream yet).
 *
 * To exclude already related records from the manage list, findExistingRelationIds() calls
 * select() on $this->relationObject->getBaseQuery(), which is the relation's own query, not a
 * copy. Since 1.2.15 the write handlers build that list query themselves, so later reads through
 * the relation in the same request load models with only an id. In onRelationManagePivotCreate
 * the added records are then saved without their other columns and fail validation (adding a
 * team to a playoff: "The title field is required.").
 *
 * After the core listener has run, swap in a fresh relation object, built the same way as
 * RelationController::initRelation() builds it.
 *
 * Use in controllers implementing Backend\Behaviors\RelationController. A controller that needs its
 * own relationExtendManageWidget() must call restoreRelationQuery() from it.
 */
trait RestoresRelationQuery
{
    public function relationExtendManageWidget($widget, $field, $model)
    {
        $this->restoreRelationQuery($widget, $field, $model);
    }

    protected function restoreRelationQuery($widget, $field, $model)
    {
        // Priority -1: listeners run in descending priority, so this runs after the core one (0).
        $widget->bindEvent('list.extendQuery', function () use ($field, $model) {
            $this->asExtension('RelationController')->relationObject = $model->{$field}();
        }, -1);
    }
}
