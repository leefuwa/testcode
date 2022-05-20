<?php
namespace App\Service\Logger;

use App\Service\Logger\Driver\Log4;
use App\Service\Logger\Driver\ThinkLog;

class LoggerFactory {

    private static $classMap = [
        'log4php'   => Log4::class,
        'think-log' => ThinkLog::class,
    ];

    private static $classTun;

    public static function getInstance($type = '', array $config = []) {
        if(self::$classTun instanceof LoggerBase) {
            return self::$classTun;
        }
        if (!isset(self::$classMap[$type])) {
            throw new \Exception($type . ' Logger is not found');
        }

        $className = self::$classMap[$type];
        self::$classTun = new $className();
        return self::$classTun;
    }


}