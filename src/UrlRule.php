<?php
namespace ancor\rest;

use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\UrlRule as _UrlRule;

/**
 * @inheritdoc
 */
class UrlRule extends _UrlRule
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->patterns['POST many'] = 'create-many';
        parent::init();
    } // end init()

}
