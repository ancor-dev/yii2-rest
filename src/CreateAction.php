<?php
namespace ancor\rest;

use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\rest\CreateAction as _CreateAction;

/**
 * @inheritdoc
 */
class CreateAction extends _CreateAction
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /* @var $model \yii\db\ActiveRecord */
        $model = $this->prepareModel([
            'scenario' => $this->scenario,
        ]);

        // do reload model from database after successful insert?
        $request = Yii::$app->request;
        $reload  = $request->get('reload') || $request->get('expand');

        $model->load($request->getBodyParams(), '');
        if ($model->save()) {

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));

            if ($reload) {
                $modelClass = $this->modelClass;
                return $modelClass::findOne($model->primaryKey);
            }
        } elseif ( ! $model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * @var callable a PHP callable that will be called to prepare an ActiveRecord model
     * If not set [[prepareModel()]] will be used instead.
     * The signature of the callable should be:
     *
     * ```php
     * function ($action, $options) {
     *     // $action is the action object currently running
     *     // $options is the options array for the ActiveRecord constructor
     * }
     * ```
     *
     * The callable should return an instance of [[ActiveRecord]].

     */
    public $prepareModel;

    /**
     * Prepare a model instance
     * @return ActiveRecord
     */
    public function prepareModel($options = [])
    {
        if ($this->prepareModel !== null) {
            return call_user_func($this->prepareModel, $this, $options);
        }

        $model = new $this->modelClass($options);
        return $model;
    } // end prepareModel()

}
