<?php
namespace ancor\rest\Serializer;

use Yii;
use yii\rest\Serializer as _Serializer;
use yii\web\HttpException;

/**
 * @inheritdoc
 */
class Serializer extends _Serializer
{

    /**
     * Serializes the given data into a format that can be easily turned into other formats.
     * This method mainly converts the objects of recognized types into array representation.
     * It will not do conversion for unknown object types or non-object data.
     * The default implementation will handle [[Model]] and [[DataProviderInterface]].
     *
     * added types:
     * + multiple creation. It's array that return from CreateAction with multiple creation scenario
     * 
     * @param mixed $data the data to be serialized.
     * @return mixed the converted data.
     */
    public function serialize($data)
    {
        $action = Yii::$app->controller->action;
        $isMany = isset($action->isMany) ? $action->isMany : false;

        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        } elseif (is_array($data) && $isMany) {
            return $this->serializeMulticreation($data);
        } else {
            return $data;
        }
    }

    /**
     * Serializes response from multiple creation
     * @param Model $model
     * @return array the array representation of the errors
     */
    protected function serializeMulticreation($model)
    {
        $this->response->setStatusCode(207, 'Multi-Status.');
        $result = [];

        foreach ($model as $one) {
            if ($one instanceof Model) {
                $hasErrors = $model->hasErrors();

                $result[] = [
                    'status' => $hasErrors ? [422, 'Data Validation Failed.'] : [201, 'Created.'],
                    'data' => $hasErrors ?
                              $this->serializeModelErrors($model) :
                              $this->serializeModel($model),
                ];
            } elseif ($one instanceof HttpException) {
                $result[] = [
                    'status' => [$one->getCode(), $one->getMessage()],
                    'data'   => null,
                ];
            } else {
                $result[] = [
                    'status' => [500, 'Internal Server Error.'],
                    'data'   => null,
                ];
            }
        }

        return $result;
    }

} // end class Serializer