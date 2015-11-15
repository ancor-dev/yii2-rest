<?php
namespace ancor\rest;

use ancor\data\ActiveDataProvider;
use ancor\model\ActiveRecord;
use yii\db\ActiveQuery;
// use yii\data\ActiveDataProvider; // current dir

use yii\helpers\ArrayHelper;
use yii\rest\ActiveController as _ActiveController;

/**
 * @inheritdoc
 */
class ActiveController extends _ActiveController
{
    /**
     * @var mixed
     */
    public $createScenario = ActiveRecord::SCENARIO_CREATE;

    /**
     * @var mixed
     */
    public $updateScenario = ActiveRecord::SCENARIO_UPDATE;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        return ArrayHelper::merge($actions, [
            'index'       => [
                'class' => 'ancor\rest\IndexAction',
            ],
            'view'        => [
                'class' => 'ancor\rest\ViewAction',
            ],
            'create'      => [
                'class' => 'ancor\rest\CreateAction',
            ],
            'create-many' => [
                'class'       => 'ancor\rest\CreateManyAction',
                'enabled'     => false,
                'modelClass'  => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario'    => $this->createScenario,
            ],
            'update'      => [
                'class' => 'ancor\rest\UpdateAction',
            ],
            'delete'      => [
                'class' => 'ancor\rest\DeleteAction',
            ],
            'options'     => [
                'class' => 'ancor\rest\OptionsAction',
            ],
        ]);
    } // end actions

    /**
     * Prepares the data provider that should return the requested collection of the models.
     * @param  ActiveQuery $model
     * @param  array       $customOptions you can override default options with the help of it
     * @return ActiveDataProvider
     */
    public static function prepareDataProvider(ActiveQuery $model, $customOptions = [])
    {
        /**
         * @var array Options for all Data Providers
         */
        static $defaultOptions = [
            'pagination' => [
                'defaultPageSize' => 15,
                'pageSizeLimit'   => [10, 50],
                'validatePage'    => false,
            ],
        ];

        $queryOption = [
            'query' => $model,
        ];

        $options = ArrayHelper::merge($defaultOptions, $queryOption, $customOptions);

        return new ActiveDataProvider($options);
    } // end prepareDataProvider

    /**
     * @inheritdoc
     */
    public function verbs()
    {
        return ArrayHelper::merge(parent::verbs(), [
            'create-many' => ['POST'],
        ]);
    } // end verbs

    /**
     * Find the model and throw exception if not found
     * @param  integer      $id the model id
     * @return ActiveRecord     instance of model
     */
    protected function findModel($id)
    {
        /**
         * @var array
         */
        static $model = [];
        if ( ! isset($model[$id])) {
            $modelClass = $this->modelClass;
            $model[$id] = $modelClass::findOne($id);
            if ( ! $model[$id]) {
                throw new NotFoundHttpException("Object not found: $id");
            }
        }

        return $model[$id];
    } // end findModel
}
