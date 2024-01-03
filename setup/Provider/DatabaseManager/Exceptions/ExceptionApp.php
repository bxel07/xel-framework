<?php

namespace Setup\Provider\DatabaseManager\Exceptions;

interface ExceptionApp
{
    public static function handleError(\Throwable $e);
}