<?php

namespace SmallStaticHttp\Kernel;

class Log
{
    const ERR_LEVEL_INFO = '[info]';
    const ERR_LEVEL_ERROR = '[error]';
    const ERR_LEVEL_CRITICAL = '[critical]';

    protected static function formatMessage(string $message, string $errorLevel): string
    {
        return (new \DateTime())->format('c') . " $errorLevel $message";
    }

    protected static function writeMessageToStd(string $message, string $errorLevel): void
    {
        if ($errorLevel != static::ERR_LEVEL_INFO) {
            fwrite(STDERR, static::formatMessage($message, $errorLevel) . "\n");
        } else {
            fwrite(STDOUT, static::formatMessage($message, $errorLevel) . "\n");
        }
    }

    public static function info(string $message): void
    {
        static::writeMessageToStd($message, static::ERR_LEVEL_INFO);
    }

    public static function error(string $message): void
    {
        static::writeMessageToStd($message, static::ERR_LEVEL_ERROR);
    }

    public static function critical(string $message): void
    {
        static::writeMessageToStd($message, static::ERR_LEVEL_CRITICAL);
    }

}