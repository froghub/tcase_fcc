<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\ArrayExpression;

/**
 * @property int $id
 * @property int $user_id
 * @property int $medicine_id
 * @property string $comment
 * @property string $period
 * @property array|ArrayExpression $times
 */
class Reminder extends ActiveRecord
{
    public $begin_date;
    public $finish_date;
    public $time;

    public static function tableName()
    {
        return '{{%reminders}}';
    }

    public function rules(): array
    {
        return [
            [['medicine_id', 'time', 'begin_date', 'finish_date'], 'required', 'message' => 'Поле {attribute} обязательно.'],
            [['medicine_id', 'user_id'], 'integer'],
            [['comment'], 'string'],

            [['begin_date', 'finish_date'], 'date', 'format' => 'php:Y-m-d'],

            ['time', 'each', 'rule' => ['string']],
            ['time', 'each', 'rule' => ['date', 'format' => 'php:H:i', 'message' => 'Неверный формат времени. Используйте ЧЧ:ММ']],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->period = "[{$this->begin_date}, {$this->finish_date}]";
        $this->times = new ArrayExpression($this->time, 'time');

        return true;
    }

    public function fields(): array
    {
        return [
            'medicine_id',
            'time' => function ($model) {
                return $model->times instanceof ArrayExpression ? $model->times->getValue() : $model->times;
            },
            'begin_date' => function ($model) {
                if (empty($model->period)) {
                    return null;
                }

                $dates = array_map('trim', explode(',', trim($model->period, '[]()')));

                return $dates[0] ?? null;
            },
            'finish_date' => function ($model) {
                if (empty($model->period)) {
                    return null;
                }

                $isUpperExcluded = str_ends_with(trim($model->period), ')');
                $dates = array_map('trim', explode(',', trim($model->period, '[]()')));
                $endDate = $dates[1] ?? null;
                if ($isUpperExcluded && $endDate) { //исправляем особенность хранения периода postgres
                    return date('Y-m-d', strtotime($endDate . ' -1 day'));
                }

                return $endDate;
            },
            'comment',
        ];
    }
}
