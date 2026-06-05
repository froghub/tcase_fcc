<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%reminder_logs}}`.
 */
class m260604_175248_create_reminder_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%reminder_logs}}', [
            'id' => $this->primaryKey(),
            'reminder_id' => $this->integer()->notNull(),
            'taken_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->addForeignKey('fk-logs-reminder_id', '{{%reminder_logs}}', 'reminder_id', '{{%reminders}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%reminder_logs}}');
    }
}
