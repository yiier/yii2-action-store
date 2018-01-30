ActionStore for Yii2
====================
ActionStore for Yii2

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiier/yii2-action-store "*"
```

or add

```
"yiier/yii2-action-store": "*"
```

to the require section of your `composer.json` file.


Migrations
-----------

Run the following command

```shell
$ php yii migrate --migrationPath=@yiier/actionStore/migrations/
```

Usage
-----

**Config**

Configure Controller class as follows : :

```php
use yiier\actionStore\actions\ActionAction;

class TopicController extends Controller
{
    public function actions()
    {
        return [
            'do' => [
                'class' => ActionAction::className(),
            ]
        ];
    }
 }
```

**Url**
 
```html
http://xxxxxxxxxxxxxx/topic/do?type=clap&model=topic&model_id=1
```

`model` recommend use `Model::tableName()`  


http response success(code==200) return json:

```json
{"code":200,"data":0,"message":"success"}
```


http response failure(code==500) return json:

```json
{"code":500,"data":"","message":"{\"model_id\":[\"Model ID不能为空。\"]}"}
```

Demo
------

**ActiveDataProvider Demo 1**

Controller

```php
<?php
use yiier\actionStore\actions\ActionAction;

class TopicController extends Controller
{
    public function actionFavorite()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ActionStore::find()
                ->where(['user_id' => user()->id, 'type' => 'favorite']),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        
        $ids = ArrayHelper::getColumn($dataProvider->getModels(), 'model_id');
        $company = ArrayHelper::index(Company::findAll($ids), 'id');

        return $this->render('favorite', [
            'dataProvider' => $dataProvider,
            'company' => $company,
        ]);
    }
 }
``` 

View


```php
<?= yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    'itemOptions' => ['class' => 'list-group-item'],
    'summary' => false,
    'itemView' => function ($model, $key, $index, $widget) use ($company) {
        return $this->render('_favorite', [
            'model' => $model,
            'key' => $key,
            'index' => $index,
            'company' => $company[$model->model_id],
        ]);
    },
    'options' => ['class' => 'list-group'],
]) ?>
```


**ActiveDataProvider Demo 2**

create ActionStoreSearch.php extends ActionStore

```php
<?php
namespace common\models;


use yiier\actionStore\models\ActionStore;

class ActionStoreSearch extends ActionStore
{
    public function getCompany()
    {
        return $this->hasOne(FundCompany::className(), ['id' => 'model_id']);
    }
}
```

Controller

```php
<?php
use yiier\actionStore\actions\ActionStoreSearch;

class TopicController extends Controller
{
    public function actionFavorite()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ActionStoreSearch::find()
                ->joinWith('company')
                ->where(['user_id' => user()->id, 'type' => 'favorite']),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
      

        return $this->render('favorite', [
            'dataProvider' => $dataProvider,
        ]);
    }
 }
```
View

```php
<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemOptions' => ['class' => 'collec-items clearfix'],
    'summary' => false,
    'itemView' => '_favorite',
    'options' => ['class' => 'collection-wrap'],
]) ?>
``` 
