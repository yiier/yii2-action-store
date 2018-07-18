<?php

use yii\db\Migration;

/**
 * Handles the creation of table `action_store`.
 */
class m171214_101829_create_action_store_table extends Migration
{
    /**
     * @var string 用户行为表
     */
    public $tableName = '{{%action_store}}';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'type' => $this->string(20)->notNull(),
            'value' => $this->integer()->defaultValue(1),
            'user_type' => $this->string(20)->defaultValue('user'),
            'user_id' => $this->integer()->notNull(),
            'model' => $this->string(20)->notNull(),
            'model_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->defaultValue(null),
            'updated_at' => $this->integer()->defaultValue(null),
        ]);

        $this->addCommentOnTable($this->tableName, '用户行为表');
        $this->createIndex('fk_model_id', $this->tableName, ['model', 'model_id', 'type']);
        $this->createIndex('fk_user_type_id', $this->tableName, ['user_type', 'user_id', 'type']);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable($this->tableName);
    }
}
