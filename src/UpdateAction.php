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
}
