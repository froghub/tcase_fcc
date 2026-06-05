<?php

namespace app\models;

use yii\db\ActiveRecord;

class ReminderLog extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%reminder_logs}}';
    }
}
