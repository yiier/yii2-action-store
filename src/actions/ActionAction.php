<?php
/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2018/1/26 18:14
 * description:
 */

namespace yiier\actionStore\actions;

use Yii;
use yii\db\Exception;
use yii\web\Response;
use yiier\actionStore\models\ActionStore;

class ActionAction extends \yii\base\Action
{
    public function init()
    {
        parent::init();
        \Yii::$app->controller->enableCsrfValidation = false;
    }

    public function run()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl)->send();
        } else {
            $model = new ActionStore();
            $model->load(array_merge(Yii::$app->request->getQueryParams(), ['user_id' => Yii::$app->user->id]), '');
            $model->validate();
            if (!$model->errors) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['code' => 200, 'data' => ActionStore::createUpdateAction($model), 'message' => 'success'];
            }
            return ['code' => 500, 'data' => '', 'message' => json_encode($model->errors)];
        }
    }
}