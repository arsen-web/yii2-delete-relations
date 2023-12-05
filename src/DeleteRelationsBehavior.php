<?php
/**
 * Created by PhpStorm.
 * User: Yarmaliuk Mikhail
 * Date: 17.08.18
 * Time: 13:12
 */

namespace MP\Yii2DeleteRelations;

use yii\base\Behavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class    DeleteRelationsBehavior
 * @package MP\Yii2DeleteRelations
 * @author  Yarmaliuk Mikhail
 * @version 1.0
 *
 * @property ActiveRecord $owner
 *
 * Support only hasOne, hasMany (not via~ relations)
 */
class DeleteRelationsBehavior extends Behavior
{
    const DELETE_BULK   = 'bulk';
    const DELETE_BY_ONE = 'by_one';

    /**
     * Relation names
     *
     * Example:
     * ['images', 'address']
     *
     * @var array
     */
    public $relations;

    /**
     * Delete or update method
     *
     * Example:
     * [
     *     'images'  => DeleteRelationsBehavior::DELETE_BULK,
     *     'address' => DeleteRelationsBehavior::DELETE_BY_ONE,
     *     'address' => null, // to keep relation record and update column to null
     * ]
     *
     * Default for each relation: DeleteRelationsBehavior::DELETE_BULK
     *
     * @var array
     */
    public $method = [];

    /**
     * @inheritdoc
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * Before delete model trigger
     *
     * @return void
     */
    public function beforeDelete(): void
    {
        foreach ($this->relations as $relationName) {
            if ($relationQuery = $this->owner->getRelation($relationName)) {
                /** @var ActiveRecord $relationClass */
                $relationClass = $relationQuery->modelClass;
                $condition     = $this->buildCondition($relationQuery);

                if (!empty($condition)) {
                    switch ($this->method[$relationName] ?? self::DELETE_BULK) {
                        case self::DELETE_BULK:
                            $relationClass::deleteAll($condition);
                        break;

                        case self::DELETE_BY_ONE:
                            foreach ($relationClass::find()->where($condition)->each() as $relatedModel) {
                                /** @var ActiveRecord $relatedModel */
                                $relatedModel->delete();
                            }
                        break;

                        // Update
                        default:
                            $attributes = \array_fill_keys(\array_keys($condition), $this->method[$relationName]);

                            $relationClass::updateAll($attributes, $condition);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Build condition to find related models
     *
     * @param ActiveQuery $relationQuery
     *
     * @return array
     */
    private function buildCondition(ActiveQuery $relationQuery): array
    {
        $condition = [];

        foreach ($relationQuery->link as $relationAttribute => $modelAttribute) {
            $condition[$relationAttribute] = $this->owner->$modelAttribute;
        };

        return $condition;
    }
}
