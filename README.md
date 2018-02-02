ActionStore for Yii2
====================
ActionStore for Yii2

[![Latest Stable Version](https://poser.pugx.org/yiier/yii2-action-store/v/stable)](https://packagist.org/packages/yiier/yii2-action-store) 
[![Total Downloads](https://poser.pugx.org/yiier/yii2-action-store/downloads)](https://packagist.org/packages/yiier/yii2-action-store) 
[![Latest Unstable Version](https://poser.pugx.org/yiier/yii2-action-store/v/unstable)](https://packagist.org/packages/yiier/yii2-action-store) 
[![License](https://poser.pugx.org/yiier/yii2-action-store/license)](https://packagist.org/packages/yiier/yii2-action-store)


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
<?php
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
POST http://xxxxxxxxxxxxxx/topic/do?type=clap&model=topic&model_id=1
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
                ->where(['user_id' => Yii::$app->user->id, 'type' => 'favorite']),
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
        return $this->hasOne(Company::className(), ['id' => 'model_id']);
    }
}
```

Controller

```php
<?php
use common\models\ActionStoreSearch;

class TopicController extends Controller
{
    public function actionFavorite()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ActionStoreSearch::find()
                ->joinWith('company')
                ->where(['user_id' => Yii::$app->user->id, 'type' => 'favorite']),
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


**actionClass Demo**

Controller

```php
<?php
use common\models\ActionStoreSearch;
use yiier\actionStore\actions\ActionAction;

class TopicController extends Controller
{
    public function actions()
    {
        return [
            'do' => [
                'class' => ActionAction::className(),
                'actionClass' => ActionStoreSearch::className()
            ]
        ];
    }
}
```

ActionStoreSearch.php

```php
<?php
use yiier\actionStore\models\ActionStore;

class ActionStoreSearch extends ActionStore
{
    /**
     * @var string
     */
    const FAVORITE_TYPE = 'favorite';

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'model_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            if ($this->type == self::FAVORITE_TYPE && $this->model == Company::tableName()) {
                Company::updateAllCounters(['favorite_count' => 1], ['id' => $this->model_id]);
            }
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if ($this->type == self::FAVORITE_TYPE && $this->model == Company::tableName()) {
            Company::updateAllCounters(['favorite_count' => -1], ['id' => $this->model_id]);
        }
    }
}
```

**resetCounter**

get user model_id count

```php
ActionStore::resetCounter(
    ActionStoreSearch::FAVORITE_TYPE,
    ['model' => Company::tableName(), 'model_id' => $company->id, 'user_id' => \Yii::$app->user->id]
);
```

get all  model_id count

```php
ActionStore::resetCounter(
    ActionStoreSearch::FAVORITE_TYPE,
    ['model' => Company::tableName(), 'model_id' => $company->id]
);
```