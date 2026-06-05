<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $description
 * @property string $dose
 */
class Medicine extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%medicines}}';
    }

    public function rules(): array
    {
        return [
            [['name','dose'],'required'],
            [['name','dose','description'],'string'],
        ];
    }
}
