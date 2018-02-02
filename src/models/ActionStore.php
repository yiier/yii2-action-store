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
     * @var string
     */
    const VIEW_TYPE = 'view';

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
                self::findOne(array_merge(['type' => self::DISLIKE_TYPE], $conditions))->delete();
                $data = array_merge(['type' => self::LIKE_TYPE], $conditions);
                break;
            case self::DISLIKE_TYPE:
                self::findOne(array_merge(['type' => self::LIKE_TYPE], $conditions))->delete();
                $data = array_merge(['type' => self::DISLIKE_TYPE], $conditions);
                break;

            default:
                $data = array_merge(['type' => $model->type], $conditions);
                break;
        }
        if ($didModel = $model::find()->filterWhere($data)->one()) {
            if (!in_array($didModel->type, [self::CLAP_TYPE, self::VIEW_TYPE])) {
                return $didModel->delete();
            } else {
                $model = $didModel;
                $data['value'] = $didModel->value + 1;
            }
        }
        $model->setAttributes($data);
        if ($model->save()) {
            unset($conditions['id'], $conditions['created_at'], $conditions['updated_at'], $conditions['value']);
            return self::resetCounter($model->type, $conditions);
        }
        throw new Exception(json_encode($model->errors));
    }


    /**
     * 返回计数器
     * @param $type
     * @param $conditions
     * @return int
     */
    public static function resetCounter($type, $conditions)
    {
        return (int)self::find()
            ->filterWhere(array_merge(['type' => $type], $conditions))
            ->select('value')
            ->sum('value');
    }
}
