<?php
namespace ancor\rest;

use Yii;
use yii\helpers\Url;
use yii\rest\CreateAction as _CreateAction;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @inheritdoc
 */
class CreateAction extends _CreateAction
{
    /**
     * @var boolean enable multiple creation
     */
    public $manyEnabled = false;
    /**
     * @var integer Limit for creating entities per a request, for multiple creation
     */
    public $manyLimit = 10;
    /**
     * @var string property name in the body of the request, for multiple creation
     */
    public $manyProperty = 'items';

    /**
     * @var boolean this request using multiple creation?
     */
    protected $isMany = false;

    /**
     * Getter for isMany
     */
    public function getIsMany()
    {
        return $this->isMany;
    } // end getIsMany()

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ( !$this->manyEnabled) return $this->createOne();

        $request      = Yii::$app->getRequest();
        $items        = $request->getBodyParam($this->manyProperty);
        $this->isMany = is_array($items) && count($request->getBodyParams()) === 1;

        return $this->isMany ? $this->createMany() : $this->createOne();
    } // end run()

    /**
     * Multiple creation
     */
    protected function createMany()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        $items = Yii::$app->getRequest()->getBodyParam($this->manyProperty);
        if ( !is_array($items)) {
            throw new BadRequestHttpException("{$this->manyProperty} must be array");
        }
        if (count($items) > $this->manyLimit) {
            throw new BadRequestHttpException("Request Entity Too Large", 413);
        }


        /* @var $model \yii\db\ActiveRecord */
        $preparedModel = $this->prepareModel([
            'scenario' => $this->scenario,
        ]);

        $request = Yii::$app->getRequest();
        $reload = $request->get('reload');

        $collection = new MultistatusCollection();
        foreach ($items as $one) {

            $model = clone $preparedModel;
            $model->load($one, '');

            if ($model->save()) {
                if ($reload) {
                    $modelClass = $this->modelClass;
                    $model      = $modelClass::findOne($model->primaryKey);
                }
            } elseif ( !$model->hasErrors()) {
                $e = new ServerErrorHttpException('Failed to create the object for unknown reason.');
                $collection->exception($e);
                continue;
            }

            $collection->inserted($model);
        }

        return $collection;
    } // end createMany()

    /**
     * Create one entity
     */
    protected function createOne()
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
        $reload  = $request->get('reload');

        $model->load($request->getBodyParams(), '');
        if ($model->save()) {

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));

            if ($reload) {
                $modelClass = $this->modelClass;
                $model      = $modelClass::findOne($model->primaryKey);
            }
        } elseif ( !$model->hasErrors()) {
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
     * function ($options, $action) {
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
    public function prepareModel(array $options = [])
    {
        if ($this->prepareModel !== null) {
            $model = call_user_func($this->prepareModel, $options, $this);
        } else {
            $model = new $this->modelClass($options);
        }

        // many fields have default values specified at database
        $model->loadDefaultValues();

        return $model;
    } // end prepareModel()

}
