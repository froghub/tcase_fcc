<?php

namespace app\services;

use app\services\NotifierInterface;
use Yii;

class LogNotifier implements NotifierInterface
{
    public function notify(string $notification): void
    {
        Yii::info($notification, 'notification');
    }

}
