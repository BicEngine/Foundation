<?php

declare(strict_types=1);

namespace Bic\Foundation\Kernel\Exception;

interface LoggerInterface
{
    /**
     * Log an exception.
     *
     * @param \Throwable $e
     *
     * @return void
     */
    public function log(\Throwable $e): void;
}
