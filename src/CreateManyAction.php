<?php
namespace ancor\rest;

use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\rest\CreateAction as _CreateAction;

/**
 * Create many entities
 * It's the same createAction, except for three differences
 * 
 * 1) Location header is not send
 * 2) Mast take 'itest' property in the body of the request
 * 3) return an array of created objects instead of an object
 */
class CreateManyAction extends _CreateAction
{
	/**
	 * Is enabled the action
	 * @var boolean
	 */
	public $enabled = true;

	/**
	 * Limit for creating entities per a request
	 * @var integer
	 */
	public $limitEntities = 10;

	/**
	 * Property name in the body of the request
	 * @var string
	 */
	public $propertyName = 'items';

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id);
		}
		if ( ! $this->enabled )
		{
      throw new MethodNotAllowedHttpException('Method Not Allowed. This url can not be handle for the entity');
		}

		$request = Yii::$app->request;
		$items = $request->getBodyParam($this->propertyName);
		if ( ! $items ) // get 'items'
		{
			throw new BadRequestHttpException("'{$this->propertyName}' is not set");
		}
		if ( ! is_array($items) )
		{
			throw new BadRequestHttpException("'{$this->propertyName}' must be an array");
		}
		if ( count($items) > $this->limitEntities )
		{
			throw new BadRequestHttpException("The number of items is limited to $this->limitEntities");
		}
		// do reload model from database after successful insert?
		$reload = $request->get('reload') || $request->get('expand');

		foreach ($items as $itemKey => $item)
		{
			/* @var $model \yii\db\ActiveRecord */
			$model = $this->prepareModel([
				'scenario' => $this->scenario,
			]);

			$model->load($item, '');
			if ($model->save()) {

				if ( $reload )
				{
					$modelClass = $this->modelClass;
					$items[$itemKey] = $modelClass::findOne($model->primaryKey);
				}
				else
				{
					$items[$itemKey] = $model;
				}
			} elseif (!$model->hasErrors()) {
				throw new ServerErrorHttpException("Failed to create the object {$itemKey} for unknown reason.");
			} else {
				// $items[$itemKey] = $model;
				return $model; // if any-one has some errors - break all
			}

		} // end foreach

		$response = Yii::$app->getResponse();
		$response->setStatusCode(201);
		return $items;
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
