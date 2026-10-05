<?php namespace App\Component\Logger;

interface MyLoggerInterface
{
    public function log( string $logData, ?string $context = null ): void;
}