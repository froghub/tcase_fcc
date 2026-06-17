<?php

use yii\db\Migration;

class m260617_130022_alter_reminders_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%reminders}}', 'comment', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%reminders}}', 'comment', $this->string()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260617_130022_alter_reminders_table cannot be reverted.\n";

        return false;
    }
    */
}
