<?php
namespace ancor\rest;

use Yii;
use yii\rest\ViewAction as _ViewAction;

/**
 * @inheritdoc
 */
class ViewAction extends _ViewAction
{
    /**
     * @var callable a PHP callable that will be called after model successful found
     * to additional checking operations. You can throw some exceptions from it
     * if the this callable will return 'false', NotFoundHttpExceptions will be throw next
     *
     * ```php
     * function ($model, $action) {
     *     if ($model->status == $model::STATUS_DELETED) return false; // Not found
     *
     *     if ($model->checking == 2) { // example
     *
     *         throw new SomeException(...);
     *     }
     * }
     * ```
     */
    public $afterFind;

    public function afterFind()
    {
        if ($this->findModel !== null) {
            return call_user_func($this->findModel, $id, $this);
        }
    }

    public function findModel($id)
    {
        $model = parent::findModel($id);

        if ($this->afterFind($model, $this) === false) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $model;
    }
}