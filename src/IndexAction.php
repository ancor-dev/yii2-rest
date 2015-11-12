<?php
namespace ancor\rest;

use Yii;
use yii\rest\IndexAction as _IndexAction;

/**
 * @inheritdoc
 */
class IndexAction extends _IndexAction
{
    /**
     * @inheritdoc
     */
    protected function prepareDataProvider()
    {
        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this);
        }

        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;

        $query = $modelClass::find();

        return ActiveController::prepareDataProvider($query);
    }
}
