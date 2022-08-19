<?php

use yii\db\Migration;

/**
 * Class m220819_015727_table_create_user
 */
class m220819_015727_table_create_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'username' => $this->string(24)->notNull(),
            'password' => $this->string(128)->notNull(),
            'authkey' => $this->string(255)->notNull(),
            'accessToken' => $this->string(255)
        ]);

        $this->insert('user', [
            'username' => 'admin',
            'password' => Yii::$app->getSecurity()->generatePasswordHash('admin'),
            'authkey' => uniqid()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user');
    }
}
