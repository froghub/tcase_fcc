<?php

namespace app\services;

use app\repositories\ReminderRepository;
use app\services\NotifierInterface;

class ReminderService
{
    private $repository;
    private $notifier;

    public function __construct(ReminderRepository $repository, NotifierInterface $notifier)
    {
        $this->repository = $repository;
        $this->notifier = $notifier;
    }


    public function checkAndNotify(): void
    {
        $currentTime = date('H:i:s');
        $tenMinutesAgo = date('H:i:s', strtotime('-10 minutes'));
        $today = date('Y-m-d');

        echo sprintf(
            "Проверка напоминаний с %s до %s...\n",
            $tenMinutesAgo,
            $currentTime
        );

        $reminds = $this->repository->findPending($today, $tenMinutesAgo, $currentTime);

        if (empty($reminds)) {
            echo "Нет доступных напоминаний для отправки.\n";
            return;
        }

        foreach ($reminds as $remind) {
            $this->notifier->notify(sprintf(
                'Напоминание: пользователю %d пора принять лекарство %d (запланировано на %s). Комментарий: %s',
                $remind['user_id'],
                $remind['medicine_id'],
                $remind['check_time'],
                $remind['comment'] ?? 'нет'
            ));
            echo "Отправлено напоминание пользователю ID: {$remind['user_id']}\n";
        }
    }
}
