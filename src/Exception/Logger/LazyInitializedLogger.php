<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception\Logger;

use Bic\Foundation\Kernel\Exception\LoggerInterface as ExceptionLoggerInterface;
use Psr\Log\LoggerInterface;

class LazyInitializedLogger implements ExceptionLoggerInterface
{
    /**
     * @var \Closure():(LoggerInterface|null)
     */
    private readonly \Closure $initializer;

    /**
     * @param callable():(LoggerInterface|null) $initializer
     */
    public function __construct(callable $initializer)
    {
        $this->initializer = $initializer(...);
    }

    /**
     * @param \Throwable $e
     *
     * @return void
     */
    public function log(\Throwable $e): void
    {
        if ($logger = $this->loadLogger()) {
            $logger->log(Severity::toLogLevel($e), $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @return LoggerInterface|null
     */
    private function loadLogger(): ?LoggerInterface
    {
        try {
            return ($this->initializer)();
        } catch (\Throwable) {
            return null;
        }
    }
}
