<?php
namespace ancor\rest;

use Yii;
use ancor\model\ActiveRecord;
use yii\rest\Action;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 * @inheritdoc
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
     * Updates an existing model or create new if it is not existing
     */
    public function run()
    {
        $this->parseRequest();
        
        $result = [];
        foreach ($this->items as $one) {

            // try to find an existing model, or create new
            $model = $this->findModel($one);
            if ( ! $model) $model = $this->prepareModel($one);

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
     * check request for errors and load items
     */
    public function init()
    {
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
     * Find model and make instance
     * @param  array $data hash-array with columns
     * @return ActiveRecordInterface|null
     */
    protected function findModel(array $data)
    {
        static $keys, $countKeys, $modelClass;

        if ($modelClass === null) {
            /* @var $modelClass ActiveRecordInterface */
            $modelClass = $this->modelClass;

            $keys = $modelClass::primaryKey();
            $keys = array_flip($keys); // the names of the keys needed for intersect_key
            $countKeys = count($keys);
        }

        $pk = array_intersect_key($data, $keys);
        if (count($pk) != $countKeys) return null;

        $model = $modelClass::findOne($pk);
        if ( ! $model) return null;

        return $model;
    } // end findModel()
    
    /**
     * Prepare a model
     * @param  array $data hash-array with columns
     * @return ActiveRecordInterface|null
     */
    protected function prepareModel()
    {
        $
    } // end prepareModel()
    
} // end class UpdateCollectionAction