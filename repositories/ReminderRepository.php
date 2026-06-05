<?php

namespace app\repositories;

use Yii;
use yii\db\Connection;

class ReminderRepository
{
    /**
     * @var Connection $db
     */
    private $db;

    public function __construct(Connection $db)
    {
//        $this->db = $db;
        $this->db = Yii::$app->db;
    }

    public function findPending(string $today, string $startTime, string $endTime) {
        $sql = "
            SELECT r.*, t.check_time
            FROM reminders r
            -- Разворачиваем массив временных отметок в строки
            CROSS JOIN LATERAL unnest(r.times) AS t(check_time)
            WHERE
                -- Проверяем, что сегодня входит в daterange
                r.period @> :today::date
                -- Проверяем, что время напоминания попало в наше 10-минутное окно
                AND t.check_time > :start_time::time
                AND t.check_time <= :end_time::time
                -- ИСКЛЮЧАЕМ тех, кто уже отметил прием в это окно (проверка на 'заранее')
                AND NOT EXISTS (
                    SELECT 1
                    FROM reminder_logs l
                    WHERE l.reminder_id = r.id
                      -- Проверяем, был ли лог сегодня в интервале +- 30 минут от времени напоминания
                      AND l.taken_at::date = :today::date
                      AND l.taken_at::time >= t.check_time - interval '30 minutes'
                      AND l.taken_at::time <= t.check_time + interval '10 minutes'
                )
        ";
        return $this->db->createCommand($sql, [
            ':today' => $today,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
        ])->queryAll();
    }
}
