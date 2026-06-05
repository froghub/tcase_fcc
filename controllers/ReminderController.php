<?php

namespace app\controllers;

use app\models\Reminder;
use app\models\ReminderLog;
use Yii;
use yii\db\ArrayExpression;
use yii\db\Expression;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

class ReminderController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class, // Только для авторизованных
        ];
        return $behaviors;
    }


    public function actionIndex(): array
    {
        $userId = Yii::$app->user->id;
        $today = date('Y-m-d');


        return Reminder::find()
            ->where(['user_id' => $userId])
            ->andWhere(new Expression('period @> :today::date', [':today' => $today]))  // оператор @> проверяет, входит ли дата в daterange
            ->all();
    }

    public function actionCreate(): array|Reminder|null
    {
        $remind = new Reminder();

        $body = Yii::$app->request->post();
        $userId = Yii::$app->user->id;

        $remind->user_id = $userId;
        $remind->medicine_id = $body['medicine_id'] ?? null;
        $remind->comment = $body['comment'] ?? null;

        $remind->begin_date = $body['begin_date'] ?? null;
        $remind->finish_date = $body['finish_date'] ?? null;
        $remind->time = $body['time'] ?? null;

        if ($remind->save()) {
            return $remind;
        }

        Yii::$app->response->statusCode = 422;
        return $remind->getErrors();
    }


    public function actionTake($id)
    {
        $reminder = Reminder::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);

        if (!$reminder) {
            throw new NotFoundHttpException('Напоминание не найдено');
        }

        $log = new ReminderLog();
        $log->reminder_id = $reminder->id;

        if ($log->save()) {
            return [
                'status' => 'success',
                'message' => 'Приём лекарства успешно отмечен',
                'taken_at' => $log->taken_at
            ];
        }

        Yii::$app->response->statusCode = 422;
        return $log->getErrors();
    }

    public function actionDelete($id): null
    {
        $reminder = Reminder::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);

        if (!$reminder) {
            throw new NotFoundHttpException('Напоминание не найдено');
        }
        $reminder->delete();
        Yii::$app->response->statusCode = 204;
        return null;
    }
}
