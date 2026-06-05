<?php

namespace app\commands;

use app\models\Medicine;
use app\models\Reminder;
use app\models\User;
use yii\console\Controller;
use yii\console\ExitCode;

class SeedController extends Controller
{
    public function actionUsers()
    {
        echo 'Users seeding...\n';

        if (User::findOne(['email' => 'm@m.m'])) {
            echo 'Пользователь m@m.m уже существует!\n';
            return ExitCode::OK;
        }

        $user = new User();
        $user->name = 'Test User';
        $user->email = 'm@m.m';

        $user->setPassword('123');

        $user->created_at = time();
        $user->updated_at = time();

        if ($user->save()) {
            echo 'Finished.\n' . json_encode(['email' => 'm@m.m', 'password' => 123], JSON_PRETTY_PRINT) . '\n';
            return ExitCode::OK;
        } else {
            echo 'Ошибка при создании пользователя:\n';
            print_r($user->getErrors()); // Выведет ошибки валидации, если они есть
            return ExitCode::DATAERR;
        }
    }

    public function actionAll()
    {
        echo 'Full seeding start';
        $user = User::findOne(['email' => 'm@m.m']);
        if (!$user) {
            $user = new User();
            $user->name = 'Test User';
            $user->email = 'm@m.m';
            $user->setPassword('123');
            $user->created_at = time();
            $user->updated_at = time();
            $user->save();
        }


        $medicine = new Medicine();
        $medicine->user_id = $user->id;
        $medicine->name = 'test medicine';
        $medicine->dose = 'test dose';
        $medicine->description = 'test description';
        $medicine->save();

        $reminder = new Reminder();
        $reminder->user_id = $user->id;
        $reminder->medicine_id = $medicine->id;
        $reminder->time = [
            date('H:i', strtotime('-10 minutes')),
            date('H:i', strtotime('+10 minutes')),
        ];
        $reminder->begin_date = date('Y-m-d');
        $reminder->finish_date = date('Y-m-d', strtotime('+1 day'));
        $reminder->comment = 'Test comment';
        $reminder->save();

        return ExitCode::OK;
    }
}
