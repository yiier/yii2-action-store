<?php
/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2018/1/26 18:14
 * description:
 */

namespace yiier\actionStore\actions;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\Response;
use yiier\actionStore\models\ActionStore;
use yiier\helpers\ArrayHelper;

class ActionAction extends \yii\base\Action
{
    /**
     * @var callable
     */
    public $successCallable;

    /**
     * @var callable
     */
    public $returnCallable;

    /**
     * @var string
     */
    public $actionClass = '\yiier\actionStore\models\ActionStore';

    /**
     * @var array Appear in pairs, only one will be recorded
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

    /**
     * @return array
     * @throws InvalidConfigException
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl)->send();
        } else {
            Yii::$app->response->format = Response::FORMAT_JSON;
            /** @var ActionStore $model */
            $model = Yii::createObject($this->actionClass);
            $model->load(Yii::$app->request->getQueryParams(), '');

            if ($model->validate()) {
                $model = ActionStore::createUpdateAction($model, $this->pairsType, $this->counterType);
                if ($this->successCallable && is_callable($this->successCallable)) {
                    call_user_func($this->successCallable, $model);
                }
                if ($this->returnCallable && is_callable($this->returnCallable)) {
                    return call_user_func($this->successCallable, $model);
                }
                $data = ArrayHelper::merge(ArrayHelper::toArray($model), ['typeCounter' => $model->getTypeCounter()]);
                return ['code' => 200, 'data' => $data, 'message' => 'success'];
            }
            if ($this->returnCallable && is_callable($this->returnCallable)) {
                return call_user_func($this->successCallable, $model);
            }
            return ['code' => 500, 'data' => '', 'message' => Json::encode($model->errors)];
        }
    }

}
