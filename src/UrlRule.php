<?php
namespace ancor\rest;

use Yii;
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
        $this->patterns = ['PATCH' => 'update-collection'] + $this->patterns;
        parent::init();
    } // end init()

}
