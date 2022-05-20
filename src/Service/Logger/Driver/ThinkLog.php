<?php

namespace App\Service\Logger\Driver;

use App\Service\Logger\LoggerBase;
use think\facade\Log;

class ThinkLog implements LoggerBase
{

    public function __construct()
    {
        Log::init([
            'default'	=>	'file',
            'channels'	=>	[
                'file'	=>	[
                    'type'	=>	'file',
                    'path'	=>	'./logs/',
                ],
            ],
        ]);
    }

    public function info($message = '')
    {
        Log::info(strtoupper($message));
    }

    public function debug($message = '')
    {
        Log::debug(strtoupper($message));
    }

    public function error($message = '')
    {
        Log::error(strtoupper($message));
    }
}