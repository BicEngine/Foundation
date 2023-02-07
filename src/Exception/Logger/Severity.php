<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception\Logger;

use Psr\Log\LogLevel;

final class Severity
{
    /**
     * @param \Throwable $e
     *
     * @return int
     */
    public static function toSeverity(\Throwable $e): int
    {
        if ($e instanceof \ErrorException) {
            return $e->getSeverity();
        }

        return \E_ERROR;
    }

    /**
     * @param \Throwable $e
     *
     * @return non-empty-string
     */
    public static function toLogLevel(\Throwable $e): string
    {
        return match (self::toSeverity($e)) {
            \E_PARSE => LogLevel::EMERGENCY,
            \E_CORE_ERROR, \E_COMPILE_ERROR, \E_ERROR => LogLevel::ALERT,
            \E_RECOVERABLE_ERROR, \E_USER_ERROR => LogLevel::ERROR,
            \E_WARNING, \E_CORE_WARNING, \E_COMPILE_WARNING, \E_USER_WARNING => LogLevel::WARNING,
            \E_NOTICE, \E_USER_NOTICE, \E_STRICT, \E_DEPRECATED, \E_USER_DEPRECATED => LogLevel::NOTICE,
            default => LogLevel::DEBUG,
        };
    }
}
