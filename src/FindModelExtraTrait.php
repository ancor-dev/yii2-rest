<?php
namespace ancor\rest;

use Yii;
use yii\web\NotFoundHttpException;

/**
 * implement extra findModel behavior
 */
trait FindModelExtraTrait
{

    /**
     * @var callable a PHP callable that will be called after model successful found
     * to additional checking operations. You can throw some exceptions from it
     * if the this callable will return 'false', NotFoundHttpExceptions will be throw next
     *
     * ```php
     * function ($model, $action) {
     *
     *     // throw NotFoundHttpException (return false)
     *     if ($model->status == $model::STATUS_DELETED) return false;
     *
     *     // throw other exception
     *     if ($model->checking == 2) {
     *         throw new SomeException(...);
     *     }
     *
     * }
     * ```
     */
    public $findModelCondition;

    public function findModelCondition($model)
    {
        if ($this->findModelCondition !== null) {
            return call_user_func($this->findModelCondition, $model, $this);
        }

        return null;
    }

    /**
     * @var callable a PHP callable that will be called after model successful found
     * to additional checking operations. You can throw some exceptions from it
     * if the this callable will return anythings, this 'anything' will return instead
     * original action operation
     *
     * ```php
     *     // return other response
     *     if ($model->options & $model::OPT_SOME_OPT) {
     *         Yii::$app->request->setStatusCode(400);
     *
     *         return [
     *             'error' => $model::ERR_SOME_ERROR,
     *         ];
     *     }
     * ```
     */
    public $afterFind;

    public function afterFind($model)
    {
        if ($this->afterFind !== null) {
            return call_user_func($this->afterFind, $model, $this);
        }

        return null;
    } // end afterFind()


    public function findModel($id)
    {
        $model = parent::findModel($id);

        if ($this->findModelCondition($model) === false) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $model;
    }

} // end class FindModelExtraBehavior