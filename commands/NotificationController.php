<?php

namespace app\commands;

use app\services\ReminderService;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class NotificationController extends Controller
{
    private ReminderService $reminderService;

    public function __construct($id, $module, ReminderService $reminderService, $config = [])
    {
        $this->reminderService = $reminderService;
        parent::__construct($id, $module, $config);
    }

    public function actionCheck()
    {
        $this->reminderService->checkAndNotify();
        return ExitCode::OK;
    }
}
