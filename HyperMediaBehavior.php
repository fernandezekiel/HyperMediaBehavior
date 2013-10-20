<?php
/**
 * behavior for generating Hypermedia-ready Objects
 *
 *
 * @author fernandezekiel/ezekielnoob <ezekiel_p_fernandez@yahoo.com>
 * @version 1.0
 * @license http://opensource.org/licenses/MIT
 *
 *
 * @property expand mixed set here the names of the related models that you want to extract the attributes with
 *
 * @todo methods for circular reference prevention
 * @todo methods for attribute selection
 */

class HyperMediaBehavior extends CActiveRecordBehavior
{

    private  $_expand = array();

    public function getExpand()
    {

        if($this->_expand !== null && $this->_expand !== '')
        {

            if(is_string($this->_expand))
            {
                $this->_expand = trim($this->_expand,' ');
                $this->_expand = explode(',',$this->_expand);
            }
            else
            {
                $this->_expand = array($this->_expand);
            }
        }
        else
        {
            $this->_expand = array();
        }
        return $this->_expand;
    }

    public function setExpand($value)
    {
        $this->_expand = $value;
    }

    /**
     * @var callable $objectFormatter formats the object before returning
     */
    public $objectFormatter;

    /**
     * @var callable $processUrl formatter function for generating urls for different models
     */
    public $processUrl;

    /**
     * sets the
     * @param array $expand determines what related models are to be expanded
     * @param callable $processUrl contains the function that determines the URL of the model if
     * null it sets it to the typical CRUD url format of yii for viewing models
     * @param callable $objectFormatter function for processing the model's attributes
     */
    public function __construct($expand = array(), $processUrl = null, $objectFormatter = null)
    {
        $this->expand = $expand;
        $this->processUrl = $processUrl;
        $this->objectFormatter = $objectFormatter;

        if($processUrl === null)
        {
            /**
             * This function will generate the typical CRUD url like 'customers/view?id=1'
             * @param $model CActiveRecord
             * @return string
             */
            $this->processUrl = function($model){
                return Yii::app()->createUrl(strtolower(get_class($model)) . "/view", array('id'=>$model->id));
            };
        }

        if($objectFormatter === null)
        {
            $this->objectFormatter = function($model, $attributes){
                return $attributes;
            };
        }
    }

    /**
     * @param int $depth how many levels of relations to open
     * @param bool $hrefOnly determines if the model attributes to be displayed are hrefOnly or all of its attributes
     * @return array
     */
    public function format($depth = 1, $hrefOnly = false)
    {
        $model = $this->owner;

        $processUrl = $this->processUrl;

        $attributes = array(
            'href' => $processUrl($model),
        );

        if(!$hrefOnly)
        {
            $attributes = CMap::mergeArray($attributes, $model->attributes);
        }

        if($depth <= 0)
        {
            return $attributes;
        }

        $relations = $model->relations();

        foreach($relations as $relationName => $relation)
        {
            if($model->getRelated($relationName) !== null)
            {
                $hrefOnly = !in_array($relationName, $this->expand);
                if(is_array($model->getRelated($relationName)))
                {
                    foreach($model->getRelated($relationName) as $relatedModel)
                    {
                        $relatedModel->attachBehavior('hyperMedia', new self($this->expand, $processUrl, $this->objectFormatter));
                        $attributes[$relationName] = $relatedModel->format($depth - 1, $hrefOnly);
                    }
                }
                else
                {
                    $relatedModel = $model->getRelated($relationName);
                    $relatedModel->attachBehavior('hyperMedia', new self($this->expand, $processUrl, $this->objectFormatter));
                    $attributes[$relationName] = $relatedModel->format($depth - 1, $hrefOnly);
                }
            }
            else
            {
                $attributes[$relationName] = null;

            }
        }

        $formatter = $this->objectFormatter;

        $attributes = $formatter($model, $attributes);
        return $attributes;
    }
}