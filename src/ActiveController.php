<?php
namespace ancor\rest;

use Yii;
use yii\rest\ActiveController as _ActiveController;
use yii\helpers\ArrayHelper;

use yii\db\ActiveQuery;
// use yii\data\ActiveDataProvider; // current dir

use ancor\model\ActiveRecord;
use ancor\data\ActiveDataProvider;

/**
 * @inheritdoc
 */
class ActiveController extends _ActiveController
{
    public $updateScenario = ActiveRecord::SCENARIO_CREATE;
    public $createScenario = ActiveRecord::SCENARIO_UPDATE;

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        return ArrayHelper::merge($actions, [
            'index' => [
                'class' => IndexAction::className(),
            ],
            'create' => [
                'class' => CreateAction::className(),
            ],
            'create-many' => [
                'class'       => CreateManyAction::className(),
                'enabled'     => false,
                'modelClass'  => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario'    => $this->createScenario,
            ],
            'update' => [
                'class' => UpdateAction::className(),
            ],
        ]);
    } // end actions

    /**
     * Find the model and throw exception if not found
     * @param  integer      $id the model id
     * @return ActiveRecord     instance of model
     */
    protected function findModel($id)
    {
        static $model = [];
        if ( ! isset($model[$id]) )
        {
            $modelClass = $this->modelClass;
            $model[$id] = $modelClass::findOne($id);
            if ( ! $model[$id] )
            {
                throw new NotFoundHttpException("Object not found: $id");
            }
        }

        return $model[$id];
    } // end findModel()
    
    /**
     * @inheritdoc
     */
    public function verbs()
    {
        return ArrayHelper::merge(parent::verbs(), [
            'create-many' => ['POST'],
        ]);
    } // end verbs()
    
    /**
     * Prepares the data provider that should return the requested collection of the models.
     * @param  ActiveQuery $model         
     * @param  array       $customOptions you can override default options with the help of it
     * @return ActiveDataProvider
     */
    public static function prepareDataProvider( ActiveQuery $model, $customOptions = [] )
    {
        // Options for all Data Providers
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
    }
 
}