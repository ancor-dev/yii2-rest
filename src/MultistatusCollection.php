<?php
namespace ancor\rest;

use yii\base\Object;
use yii\db\ActiveRecordInterface;

/**
 * This class is used to store a collection which is obtained after the multi-creation
 * entities or multi-update entities.
 *
 * entities can be only add. Can not be removed.
 */
class MultistatusCollection extends Object implements \IteratorAggregate
{
    /**
     * @var array collection of objects
     */
    private $collection = [];

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collection);
    } // end getIterator()
    


    /**
     * Add model with flag 'insert'
     * @param  ActiveRecordInterface $model
     */
    public function inserted(ActiveRecordInterface $model)
    {
        $this->collection[] = ['insert', $model];
    } // end inserted()

    /**
     * Add model with flag 'update'
     * @param  ActiveRecordInterface $model
     */
    public function updated(ActiveRecordInterface $model)
    {
        $this->collection[] = ['update', $model];
    } // end updated()

    /**
     * Add model with flag 'exception'
     * @param  ActiveRecordInterface $model
     */
    public function exception(\Exception $e)
    {
        $this->collection[] = ['exception', $e];
    } // end exception()
    
} // end class Collection