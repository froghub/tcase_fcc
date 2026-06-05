<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class NotificationController extends Controller
{
    public function actionCheck()
    {
        $db = Yii::$app->db;

        $currentTime = date('H:i:s');
        $tenMinutesAgo = date('H:i:s', strtotime('-10 minutes'));
        $today = date('Y-m-d');

        Yii::info('[ ' . date('Y-m-d H:i:s') . " ] Проверка напоминаний в интервале с $tenMinutesAgo по $currentTime...\n", 'console');
        // UNNEST() для разворачивания массива time[] из Postgres во временные строки
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

        $reminders = $db->createCommand($sql, [
            ':today' => $today,
            ':start_time' => $tenMinutesAgo,
            ':end_time' => $currentTime,
        ])->queryAll();

        if (empty($reminders)) {
            Yii::info("Нет активных напоминаний для отправки.\n", 'console');
            return ExitCode::OK;
        }

        foreach ($reminders as $reminder) {
            Yii::info(sprintf(
                "УВЕДОМЛЕНИЕ: Пользователю %d пора принять лекарство %d (Запланировано на %s). Комментарий: %s\n",
                $reminder['user_id'],
                $reminder['medicine_id'],
                $reminder['check_time'],
                $reminder['comment'] ?? 'нет'
            ), 'console');
        }

        return ExitCode::OK;
    }
}
