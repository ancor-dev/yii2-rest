<?php
namespace ancor\rest;

use Yii;
use yii\rest\DeleteAction as _DeleteAction;
use yii\web\ServerErrorHttpException;

/**
 * @inheritdoc
 */
class DeleteAction extends _DeleteAction
{
    use FindModelExtraTrait;

    /**
     * afterFind added
     *
     * @param mixed $id
     *
     * @return mixed|void
     * @throws \yii\web\ServerErrorHttpException
     */
    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        $responseReplacement = $this->afterFind($model);
        if ( !$responseReplacement) return $responseReplacement;

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }
}