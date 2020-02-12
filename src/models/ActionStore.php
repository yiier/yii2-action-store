<?php

namespace yiier\actionStore\models;

use Yii;
use yii\behaviors\TimestampBehavior;

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
}
