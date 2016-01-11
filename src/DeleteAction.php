<?php
namespace ancor\rest;

use Yii;
use yii\rest\DeleteAction as _DeleteAction;

/**
 * @inheritdoc
 */
class DeleteAction extends _DeleteAction
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

    public function afterFind($model)
    {
        if ($this->afterFind !== null) {
            return call_user_func($this->afterFind, $model, $this);
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