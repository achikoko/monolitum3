<?php

namespace monolitum\bootstrap\notification;

use monolitum\bootstrap\manager\Notification_Manager;
use monolitum\core\Active;
use monolitum\core\Find;
use monolitum\backend\Manager;
use monolitum\core\MNode;

class NotificationManager extends MNode
{

    const TYPE_SUCCESS = "success";
    const TYPE_WARNING = "warning";
    const TYPE_ERROR = "error";


    public function __construct($builder = null)
    {
        parent::__construct($builder);
    }

    /**
     * @param string $type
     * @param string $text
     * @return void
     */
    public function showNotification(string $type, string $text): void
    {
        error_log($text);
    }

    /**
     * @param string $type
     * @param string $text
     * @return void
     */
    public static function pushShowNotification(string $type, string $text): void
    {
        /** @var NotificationManager $entities */
        $entities = Find::pushAndGet(NotificationManager::class);
        $entities->showNotification($type, $text);
    }

}
