<?php

namespace Setup\Provider\DatabaseManager\Exceptions;

class ErrorHandler implements ExceptionApp
{

    public static function handleError(\Throwable $e): void
    {
       $logPath = require __DIR__.'/Log/error.log';
       $logMsg= "Error : ".$e->getMessage();
       error_log($logMsg,3, $logPath);
    }
}