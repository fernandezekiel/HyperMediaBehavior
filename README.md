HyperMediaBehavior
==================
Behavior for CActiveRecords for generating 'href' attribute to model attributes for REST API applications


Usage
==================
```php
<?php
//this is a modified version of loadModel from yiiwheels

public function actionView($id)
{
        $model = $this->loadModel($id)
        //transferring $this into $controller since we cannot use $this in a closure
        $controller = $this;
        
        //attach the behavior here
        $model->attachBehavior('hyperMedia', new HyperMediaBehavior($_GET['expand'], function($model) use ($controller){
            return $controller->createUrl('view', array('model'=>strtolower(get_class($model)),'id'=>$model->id));
        }));

        //object formatter can apply an extra layer of security, reformatting of data.
        /
        $model->objectFormatter = function($model, $attributes){
            if(get_class($model) == 'User')
            {
                unset($attributes['password']);
                unset($attributes['activation_key']);
            }
            return $attributes;
        };
        
        echo json_encode($model->format());
}

```

**Example Output**
Request:
GET http://localhost/myApp/api/v1/user/1?expand=profile
```json
{
        href: "/myApp/api/v1/user/1",
        id: "1",
        username: "admin",
        email: "admin@myApp.biz",
        created_at: "2013-10-11 21:04:52",
        last_visit: "0000-00-00 00:00:00",
        status: "1",
        profile: {
                href: "/myApp/api/v1/profile/1",
                id: "1",
                user_id: "1",
                first_name: "admin",
                middle_name: null,
                last_name: "admin",
                image_path: null
        }
}
```
expand is a coma separated string that has the names of the relations to be expanded,
if expand is not set 
