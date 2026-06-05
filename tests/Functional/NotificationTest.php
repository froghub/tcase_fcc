<?php

namespace Functional;

use app\models\Medicine;
use Codeception\Test\Unit;
use Yii;
use app\models\User;
use app\models\Reminder;
use yii\log\Logger;

class NotificationTest extends Unit
{
    protected function _before()
    {
        // Включена полная чистка базы. пока выключаю
//        Yii::$app->db->createCommand('TRUNCATE users RESTART IDENTITY CASCADE')->execute();
    }

    public function testNotificationTriggersCorrectly()
    {
        Yii::$app->controllerMap = [
            'notification' => 'app\commands\NotificationController',
        ];

        /**
         * Тут впустую потрачен час. Может и больше
         * Почему? Я решил что не гоже писать в лог контейнера и решил в лог фреймворка написать
         * а gemini убедил меня что с легкостью мне поможет мокнуть логгер. В очередной раз он облажался
         */
        $loggerMock = new class extends Logger {
            public $capturedMessages = [];
            public function log($message, $level, $category = 'application'): void
            {
                $this->capturedMessages[] = [
                    'message' => is_array($message) ? json_encode($message) : (string) $message,
                    'level' => $level,
                    'category' => $category,
                    'time' => microtime(true),
                ];
                parent::log($message, $level, $category);
            }
            public function clearCaptured(): void
            {
                $this->capturedMessages = [];
            }
        };
        Yii::setLogger($loggerMock);


        $user = new User();
        $user->name = 'test_patient';
        $user->email = 'patient@test.com';
        $user->setPassword('123');
        $user->created_at = time();
        $user->updated_at = time();
        $this->assertTrue($user->save());


        $medicine = new Medicine();
        $medicine->user_id = $user->id;
        $medicine->name = 'test medicine';
        $medicine->dose = 'test dose';
        $medicine->description = 'test description';
        $this->assertTrue($medicine->save());


        $reminder = new Reminder();
        $reminder->user_id = $user->id;
        $reminder->medicine_id = $medicine->id;
        $reminder->begin_date = date('Y-m-d', strtotime('-1 day'));
        $reminder->finish_date = date('Y-m-d', strtotime('+1 day'));
        // Подстраиваем время на 5 минут назад, чтобы оно гарантированно попало в 10-минутное окно
        // Если хочется убедиться, что срабатывает уведомление только когда надо - можно подвигать окно
        $reminder->time = [date('H:i', strtotime('-5 minutes'))];
        $reminder->comment = 'test comment';
        $this->assertTrue($reminder->save(), 'Unable to save reminder');

        Yii::$app->runAction('notification/check');

        $logs = $loggerMock->capturedMessages;
        $this->assertNotEmpty($logs, 'Логгер не поймал ни одного сообщения.');

        $hasNotification = false;
        foreach ($logs as $msg) {
            if (str_contains($msg['message'], 'УВЕДОМЛЕНИЕ:') && str_contains($msg['message'], 'Пользователю ' . $user->id)) {
                $hasNotification = true;
                break;
            }
        }
        $this->assertTrue($hasNotification, 'Уведомление с правильным текстом не было найдено в логах.');
    }
}
