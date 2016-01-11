<?php
namespace ancor\rest;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\web\ServerErrorHttpException;
use yii\rest\UpdateAction as _UpdateAction;

/**
 * added 'reload' attribute
 */
class UpdateAction extends _UpdateAction
{
    /**
     * @inheritdoc
     */
    public function run($id)
    {
        $model = parent::run($id);

        // do reload model from database after successful insert?
        $request = Yii::$app->request;
        $reload  = $request->get('reload') || $request->get('expand');

        if ($reload) {
            $modelClass = $this->modelClass;
            $model      = $modelClass::findOne($model->primaryKey);
        }

        return $model;
    }
    
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
