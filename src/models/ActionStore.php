<?php

namespace yiier\actionStore\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%action_store}}".
 *
 * @property int $id
 * @property string $type
 * @property integer $value
 * @property string $user_type
 * @property int $user_id
 * @property string $model
 * @property int $model_id
 * @property int $created_at
 * @property int $updated_at
 */
class ActionStore extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%action_store}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'model', 'model_id'], 'required'],
            [['user_id', 'model_id', 'value', 'created_at', 'updated_at'], 'integer'],
            ['user_type', 'default', 'value' => 'user'],
            [['type', 'user_type', 'model'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'value' => Yii::t('app', 'Value'),
            'user_type' => Yii::t('app', 'User Type'),
            'user_id' => Yii::t('app', 'User ID'),
            'model' => Yii::t('app', 'Model'),
            'model_id' => Yii::t('app', 'Model ID'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }


    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->user_id = $this->user_id ?: \Yii::$app->user->id;
            return true;
        } else {
            return false;
        }
    }


    /**
     * 获取计数器
     * @param $type
     * @param $conditions
     * @return int
     */
    public static function getCounter($type, $conditions)
    {
        return (int)self::find()
            ->filterWhere(array_merge(['type' => $type], $conditions))
            ->select('value')
            ->sum('value');
    }


    /**
     * @param $model ActionStore
     * @param array $pairsType
     * @param array $counterType
     * @return ActionStore
     * @throws Exception
     * @throws \yii\db\StaleObjectException
     * @throws \Throwable
     */
    public static function createUpdateAction($model, $pairsType = [], $counterType = [])
    {
        $conditions = array_filter($model->attributes);
        $pairsType0 = ArrayHelper::getValue($pairsType, 0);
        $pairsType1 = ArrayHelper::getValue($pairsType, 1);
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
            if (in_array($didModel->type, $counterType)) {
                $model = $didModel;
                $data['value'] = $didModel->value + 1;
            } else {
                $didModel->delete();
                return $model;
            }
        }
        $model->setAttributes($data);
        if ($model->save()) {
            return $model;
        }
        throw new Exception(json_encode($model->errors));
    }

    public function getTypeCounter()
    {
        $conditions = [
            'user_type' => $this->user_type,
            'user_id' => $this->user_id,
            'model' => $this->model,
            'model_id' => $this->model_id,
        ];
        return ActionStore::getCounter($this->type, $conditions);
    }

}
