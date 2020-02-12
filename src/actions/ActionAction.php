<?php
/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2018/1/26 18:14
 * description:
 */

namespace yiier\actionStore\actions;

use Yii;
use yii\db\Exception;
use yii\helpers\Json;
use yii\web\Response;
use yiier\actionStore\models\ActionStore;

class ActionAction extends \yii\base\Action
{
    /**
     * @var string
     */
    public $actionClass = '\yiier\actionStore\models\ActionStore';

    /**
     * @var string Appear in pairs, only one will be recorded
     */
    public $pairsType = ['like', 'dislike'];

    /**
     * @var array counter type no delete
     */
    public $counterType = ['clap', 'view'];

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
            Yii::$app->response->format = Response::FORMAT_JSON;
            /** @var ActionStore $model */
            $model = Yii::createObject($this->actionClass);
            $model->
            $model->load(array_merge(Yii::$app->request->getQueryParams(), ['user_id' => Yii::$app->user->id]), '');

            if ($model->validate()) {
                return ['code' => 200, 'data' => $this->createUpdateAction($model), 'message' => 'success'];
            }
            return ['code' => 500, 'data' => '', 'message' => Json::encode($model->errors)];
        }
    }


    /**
     * @param $model ActionStore
     * @return int
     * @throws Exception
     * @throws \Throwable
     */
    protected function createUpdateAction($model)
    {
        $conditions = array_filter($model->attributes);
        $pairsType0 = $this->pairsType[0];
        $pairsType1 = $this->pairsType[1];
        switch ($model->type) {
            case $pairsType0:
                $model::find()->where(array_merge(['type' => $pairsType1], $conditions))->one()->delete();
                $data = array_merge(['type' => $pairsType0], $conditions);
                break;
            case $pairsType1:
                $model::find()->where(array_merge(['type' => $pairsType0], $conditions))->one()->delete();
                $data = array_merge(['type' => $pairsType1], $conditions);
                break;

            default:
                $data = array_merge(['type' => $model->type], $conditions);
                break;
        }
        if ($didModel = $model::find()->filterWhere($data)->one()) {
            if (!in_array($didModel->type, [$this->counterType])) {
                return $didModel->delete();
            } else {
                $model = $didModel;
                $data['value'] = $didModel->value + 1;
            }
        }
        $model->setAttributes($data);
        if ($model->save()) {
            unset($conditions['id'], $conditions['created_at'], $conditions['updated_at'], $conditions['value']);
            return $model::getCounter($model->type, $conditions);
        }
        throw new Exception(json_encode($model->errors));
    }
}