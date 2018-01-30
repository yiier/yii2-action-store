<?php

namespace yiier\actionStore\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

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
     * @var string
     */
    const LIKE_TYPE = 'like';

    /**
     * @var string
     */
    const DISLIKE_TYPE = 'dislike';

    /**
     * @var string
     */
    const CLAP_TYPE = 'clap';

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
            [['type', 'user_type', 'model'], 'string', 'max' => 255],
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
     * @param $model ActionStore
     * @return int
     * @throws Exception
     */
    public static function createUpdateAction($model)
    {
        $conditions = array_filter($model->attributes);
        switch ($model->type) {
            case self::LIKE_TYPE:
                self::deleteAll(array_merge(['type' => self::DISLIKE_TYPE], $conditions));
                $data = array_merge(['type' => self::LIKE_TYPE], $conditions);
                break;
            case self::DISLIKE_TYPE:
                self::deleteAll(array_merge(['type' => self::LIKE_TYPE], $conditions));
                $data = array_merge(['type' => self::DISLIKE_TYPE], $conditions);
                break;

            default:
                $data = array_merge(['type' => $model->type], $conditions);
                break;
        }
        if ($value = self::find()->filterWhere($data)->select('value')->scalar()) {
            if ($model->type == self::CLAP_TYPE) {
                $model->value = $value + 1;
            } else {
                self::find()->filterWhere($data)->one()->delete();
                return 0;
            }
        }
        self::deleteAll($data);
        $model->load($data, '');
        if ($model->save()) {
            return (int)$model->resetCounter();
        }
        throw new Exception(json_encode($model->errors));
    }

    /**
     * 返回计数器
     * @return int
     */
    public function resetCounter()
    {
        $data = $this->attributes;
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['value']);
        return self::find()->filterWhere($data)->select('value')->scalar();
    }
}
