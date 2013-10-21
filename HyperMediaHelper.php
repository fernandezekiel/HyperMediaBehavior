<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Web Developer
 * Date: 10/21/13
 * Time: 9:37 PM
 * To change this template use File | Settings | File Templates.
 */

class HyperMediaHelper
{
    public static $paginationAttributes = array(
        'next' => null,
        'previous' => null,
        'first' => null,
        'last' => null
    );

    /**
     * @param $model CActiveRecord|CActiveRecord[]|CActiveDataProvider
     */
    public static function format($model, $params = array())
    {

        $attributes = array();

        $paginationAttributes = self::$paginationAttributes;


        $defaultParams = array(
            'expand' => null,
            'listKey' => 'items',
            'offset' => 0,
            'limit' => 10,
            'modelUrl' => null,
            'paginationUrl' => function() use ($paginationAttributes)
            {
                return $paginationAttributes;
            },
            'objectFormatter' => null,
        );

        if($model instanceof CActiveDataProvider)
        {
            $defaultParams = CMap::mergeArray($defaultParams,array(
                'listKey' => $model->modelClass,
                'offset'=> $model->pagination->offset,
                'limit' => $model->pagination->limit,
            ));

            $model = $model->getData();
        }

        $params = CMap::mergeArray($defaultParams, $params);


        if(is_array($model))
        {
            $attributes = CMap::mergeArray($params['paginationUrl']($model), $attributes);
            $attributes[$params['listKey']] = array();
            $models = $model;
            foreach($models as $model)
            {
                $model->attachBehavior('hyperMedia', new HyperMediaBehavior($params['expand']), $params['modelUrl']);
                array_push($attributes[$params['listKey']], $model->format(1, !in_array($params['listKey'], $model->expand)));
            }
        }

        else if ($model instanceof CActiveRecord)
        {
            $model->attachBehavior('hyperMedia', new HyperMediaBehavior($params['expand']), $params['modelUrl']);
            array_push($attributes, $model->format());
        }
        return $attributes;
    }
}