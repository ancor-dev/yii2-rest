<?php
namespace ancor\rest;

use Yii;
use yii\rest\ViewAction as _ViewAction;

/**
 * @inheritdoc
 */
class ViewAction extends _ViewAction
{
    use FindModelExtraTrait;

    // /**
    //  * @inheritdoc
    //  */
    // public function behaviors()
    // {
    //   return ArrayHelper::merge(parent::behaviors(), [
    //     FindModelExtraBehavior::className(),
    //   ]);
    // } // end behaviors()


    /**
     * @inheritdoc
     */
    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        $responseReplacement = $this->afterFind($model);

        return $responseReplacement === null ? $model : $responseReplacement;
    } // end run()

}