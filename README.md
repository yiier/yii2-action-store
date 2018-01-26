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

OConfigure Controller class as follows : :

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

 Url
 
```html
http://xxxxxxxxxxxxxx/topic/do?type=clap&model=topic&model_id=1
```