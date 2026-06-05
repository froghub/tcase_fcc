<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%medicines}}`.
 */
class m260604_152603_create_medicines_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%medicines}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->string()->notNull(),
            'dose' => $this->string()->notNull(),
        ]);
        $this->addForeignKey('fk-medicines-user_id', '{{%medicines}}', 'user_id', '{{%users}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%medicines}}');
    }
}
