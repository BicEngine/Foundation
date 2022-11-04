<?php

declare(strict_types=1);

namespace Bic\Foundation\Kernel;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class ErrorLogger
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * @param \Throwable $e
     *
     * @return void
     */
    public function log(\Throwable $e): void
    {
        if ($logger = $this->getLogger()) {
            $logger->log($this->getErrorLevelOf($e), $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @return LoggerInterface|null
     */
    private function getLogger(): ?LoggerInterface
    {
        try {
            if ($this->container->has(LoggerInterface::class)) {
                /** @var LoggerInterface */
                return $this->container->get(LoggerInterface::class);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    /**
     * @param \Throwable $e
     *
     * @return int
     */
    private function getErrorSeverityOf(\Throwable $e): int
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
    private function getErrorLevelOf(\Throwable $e): string
    {
        return match ($this->getErrorSeverityOf($e)) {
            \E_PARSE => LogLevel::EMERGENCY,
            \E_CORE_ERROR, \E_COMPILE_ERROR, \E_ERROR => LogLevel::ALERT,
            \E_RECOVERABLE_ERROR, \E_USER_ERROR => LogLevel::ERROR,
            \E_WARNING, \E_CORE_WARNING, \E_COMPILE_WARNING, \E_USER_WARNING => LogLevel::WARNING,
            \E_NOTICE, \E_USER_NOTICE, \E_STRICT, \E_DEPRECATED, \E_USER_DEPRECATED => LogLevel::NOTICE,
            default => LogLevel::DEBUG,
        };
    }
}

