<?php
namespace App\Service\Logger;

interface LoggerBase {

    public function __construct();

    public function info($message = '');

    public function debug($message = '');

    public function error($message = '');
}