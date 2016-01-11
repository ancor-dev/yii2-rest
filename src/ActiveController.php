<?php
namespace ancor\rest;

use ancor\data\ActiveDataProvider;
use ancor\model\ActiveRecord;
use yii\db\QueryInterface;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController as _ActiveController;
use yii\web\NotFoundHttpException;

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
            'index'             => [
                'class' => 'ancor\rest\IndexAction',
            ],
            'view'              => [
                'class' => 'ancor\rest\ViewAction',
            ],
            'create'            => [
                'class' => 'ancor\rest\CreateAction',
            ],
            'update'            => [
                'class' => 'ancor\rest\UpdateAction',
            ],
            'update-collection' => [
                'class'          => 'ancor\rest\UpdateCollectionAction',
                'modelClass'     => $this->modelClass,
                'updateScenario' => $this->updateScenario,
                'createScenario' => $this->createScenario,
                'checkAccess'    => [$this, 'checkAccess'],
            ],
            'delete'            => [
                'class' => 'ancor\rest\DeleteAction',
            ],
            'options'           => [
                'class' => 'ancor\rest\OptionsAction',
            ],
        ]);
    } // end actions

    public $serializer = [
        'class' => 'ancor\rest\Serializer',
    ];

    /**
     * Prepares the data provider that should return the requested collection of the models.
     *
     * @param  QueryInterface $model
     * @param  array          $customOptions you can override default options with the help of it
     *
     * @return ActiveDataProvider
     */
    public static function prepareDataProvider(QueryInterface $model, $customOptions = [])
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
        return parent::verbs() + [
            'update-collection' => ['PATCH'],
        ];
    } // end verbs

    /**
     * Find the model and throw exception if not found
     *
     * @param  integer $id the model id
     *
     * @return \ancor\model\ActiveRecord instance of model
     * @throws \yii\web\NotFoundHttpException
     */
    protected function findModel($id)
    {
        /**
         * @var array
         */
        static $model = [];
        if ( !isset($model[$id])) {
            $modelClass = $this->modelClass;
            $model[$id] = $modelClass::findOne($id);
            if ( !$model[$id]) {
                throw new NotFoundHttpException("Object not found: $id");
            }
        }

        return $model[$id];
    } // end findModel
}
