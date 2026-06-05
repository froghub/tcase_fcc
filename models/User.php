<?php

declare(strict_types=1);

namespace app\models;

use Firebase\JWT\JWT;
use Firebase\JWT\Key as JwtKey;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property int $created_at
 * @property int $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): static|null
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): static|null
    {
        try {
            $secret = $_ENV['JWT_SECRET_KEY'] ?? 'fallback_secret';
            $decoded = JWT::decode($token, new JwtKey($secret, 'HS256'));
            return static::findOne($decoded->uid);
        } catch (\Exception $e) {
            error_log('JWT Validation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateJwt(): string
    {
        $secretKey = $_ENV['JWT_SECRET_KEY'] ?? 'fallback_secret';

        $payload = [
            'iss' => 'localhost', //кто
            'aud' => 'localhost', //для кого
            'iat' => time(), //время
            'exp' => time() + 3600, //годность
            'uid' => $this->id
        ];
        return JWT::encode($payload, $secretKey, 'HS256');
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail(string $email): static|null
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string|null
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return false;
    }

    public function setPassword($password): void
    {
        $this->password_hash = \Yii::$app->security->generatePasswordHash($password);
    }
    public function validatePassword($password): bool
    {
        return \Yii::$app->security->validatePassword($password, $this->password_hash);
    }
}
