<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%reminders}}`.
 */
class m260604_154116_create_reminders_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%reminders}}', [
            'id' => $this->primaryKey(),
            'medicine_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'comment' => $this->string()->notNull(),
            'period' => 'daterange NOT NULL',
            'times' => 'time[]'
        ]);

        $this->execute('CREATE INDEX idx_reminders_period ON reminders USING gist (period);');

        $this->addForeignKey('fk-reminders-user_id', '{{%reminders}}', 'user_id', '{{%users}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-reminders-medicine_id', '{{%reminders}}', 'medicine_id', '{{%medicines}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%reminders}}');
    }
}
