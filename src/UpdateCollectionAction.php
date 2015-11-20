<?php
namespace ancor\rest;

use Yii;
use ancor\model\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\rest\Action;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Update collection.
 * This action update existing entities and create new if it is not exists
 */
class UpdateCollectionAction extends Action
{
    /**
     * @var string the scenario used for updating a model.
     */
    public $updateScenario = ActiveRecord::SCENARIO_UPDATE;
    /**
     * @var string the scenario used for creating a model.
     */
    public $createScenario = ActiveRecord::SCENARIO_CREATE;

    /**
     * @var boolean enable this action
     */
    public $enable = false;
    /**
     * @var integer Limit for processing entities per a request
     */
    public $limit = 10;
    /**
     * @var string property name in the body of the request
     */
    public $property = 'items';
    
    /**
     * @var array items from request
     */
    protected $items;

    /**
     * @var callable a PHP callable that will be called to pre-processing the model and set some
     * options to it. If not set, nothing will called.
     * The signature of the callable should be:
     *
     * ```php
     * function (ActiveRecord $model, $action) {
     *     // $model is instance of ActiveRecord model.
     *     // It is can be new row(created by prepareModel()) or finded model(created by tryFindModel()).
     *     // $action is the action object currently running
     *
     *     $model->user_id = Yii::$app->request->get('user_id');
     * }
     * ```
     *
     * The callable should return the model found, or throw an exception if not found.
     */
    public $preProcessingModel;

    /**
     * check request for errors and load items
     */
    public function init()
    {
        if ( ! $this->enable) {
            throw new MethodNotAllowedHttpException("Method Not Allowed");
        }

        $this->items = Yii::$app->getRequest()->getBodyParam($this->property);

        if ( ! is_array($this->items)) {
            throw new BadRequestHttpException("{$this->property} must be array");
        }

        if (count($this->items) > $this->limit) {
            throw new BadRequestHttpException("Request Entity Too Large", 413);
        }

        parent::init();
    } // end parseRequest()

    /**
     * Updates an existing model or create new if it is not existing
     */
    public function run()
    {
        $result = [];

        foreach ($this->items as $one) {

            // try to find an existing model, or create new
            $model = $this->tryFindModel($one);
            if ( ! $model) $model = $this->prepareModel();

            if ($this->preProcessingModel !== null) {
                call_user_func($this->preProcessingModel, $model, $this);
            }

            try {
                if ($this->checkAccess) {
                    call_user_func($this->checkAccess, $this->id, $model);
                }

                $model->load($one, '');
                if ($model->save() === false && !$model->hasErrors()) {
                    throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
                }
            } catch (HttpException $e) {
                $result = $e;
            }

            $result[] = $model;
        }

        return $result;
    }
    
    /**
     * @var callable a PHP callable that will be called to return the model corresponding
     * to the specified primary key value. If not set, [[tryFindModel()]] will be used instead.
     * The signature of the callable should be:
     *
     * ```php
     * function (array $data, $action) {
     *     // $data is the hash-array model from request. It must using only for find model, don't
     *     // use it for filling thi model.
     *     // $action is the action object currently running
     * }
     * ```
     *
     * The callable should return the model found, or throw an exception if not found.
     */
    public $tryFindModel;

    /**
     * Find model and make instance
     * @param  array $data hash-array with columns
     * @return ActiveRecordInterface|null
     */
    public function tryFindModel(array $data)
    {
        static $keys, $countKeys, $modelClass;

        if ($modelClass === null) {

            /* @var $modelClass ActiveRecordInterface */
            $modelClass = $this->modelClass;

            $keys = $modelClass::primaryKey();
            $keys = array_flip($keys); // the names of the keys needed for intersect_key
            $countKeys = count($keys);
        }

        if ($this->tryFindModel !== null) {
            $model = call_user_func($this->tryFindModel, $data, $this);
        } else {
            $pk = array_intersect_key($data, $keys);
            if (count($pk) != $countKeys) return null;

            $model = $modelClass::findOne($pk);
        }

        if ( ! $model) return null;
        $model->scenario = $this->updateScenario;

        return $model;
    } // end tryFindModel()

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
        $options = ArrayHelper::merge(['scenario' => $this->createScenario], $options);

        if ($this->prepareModel !== null) {
            $model = call_user_func($this->prepareModel, $options, $this);
        } else {
            $model = new $this->modelClass($options);
        }

        // many fields have default values specified at database
        $model->loadDefaultValues();

        return $model;
    } // end prepareModel()
    
} // end class UpdateCollectionAction