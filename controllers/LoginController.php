<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class LoginController extends Controller
{
    /**
     * @throws UnauthorizedHttpException
     * @throws BadRequestHttpException
     */
    public function actionLogin(): array
    {
        $body = Yii::$app->request->post();

        $email = $body['email'] ?? null;
        $password = $body['password'] ?? null;

        if (!$email || !$password) {
            throw new BadRequestHttpException('Email and password are required');
        }

        $user = User::findOne(['email' => $email]);

        if (!$user || !$user->validatePassword($password)) {
            throw new UnauthorizedHttpException('Invalid email or password');
        }

        $token = $user->generateJwt();

        return [
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ];
    }
}
