<?php
namespace ancor\rest;

use Yii;
use yii\db\ActiveRecord;
use yii\rest\UpdateAction as _UpdateAction;
use yii\web\ServerErrorHttpException;

/**
 * added 'reload' attribute
 */
class UpdateAction extends _UpdateAction
{
    use FindModelExtraTrait;

    /**
     * @inheritdoc
     */
    public function run($id)
    {
        /* @var $model ActiveRecord */
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        $responseReplacement = $this->afterFind($model);
        if ($responseReplacement !== null) return $responseReplacement;

        $model->scenario = $this->scenario;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        // do reload model from database after successful insert?
        $request = Yii::$app->request;
        $reload  = $request->get('reload');

        if ($reload) {
            $modelClass = $this->modelClass;
            $model      = $modelClass::findOne($model->primaryKey);
        }

        return $model;
    }

}