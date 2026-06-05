<?php

namespace app\controllers;

use app\models\Medicine;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;

class MedicineController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
        ];
        return $behaviors;
    }

    public function actionIndex(): array
    {
        $userId = Yii::$app->user->id;
        return Medicine::find()
            ->where(['user_id' => $userId])
            ->all();
    }

    public function actionCreate(): Medicine|array|null
    {
        $model = new Medicine();
        $model->user_id = Yii::$app->user->id;
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        Yii::$app->response->statusCode = 422;
        return $model->getErrors();
    }
}
