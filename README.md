HyperMediaBehavior
==================
Behavior for CActiveRecords for generating 'href' attribute to model attributes for REST API applications


Usage
==================
```php
<?php
//this is a modified version of loadModel from yiiwheels

public function loadModel($class, $id, $criteria = array(), $exceptionOnNull = true)
{
        $model = parent::loadModel($class, $id, $criteria, $exceptionOnNull);
        //transferring $this into $controller since we cannot use $this in a closure
        $controller = $this;
        
        //attach the behavior here
        $model->attachBehavior('hyperMedia', new HyperMediaBehavior($this->expand, function($model) use ($controller){
            return $controller->createUrl('view', array('model'=>strtolower(get_class($model)),'id'=>$model->id));
        }));

        $model->objectFormatter = function($model, $attributes){
            if(get_class($model) == 'User')
            {
                unset($attributes['password']);
                unset($attributes['activation_key']);
            }
            return $attributes;
        };
        return $model->format();
}

$model = $this->loadModel('Customer', $id);
echo json_encode($model);
```
