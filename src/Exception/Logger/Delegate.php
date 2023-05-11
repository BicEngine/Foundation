<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception\Logger;

use Bic\Foundation\Exception\LoggerInterface;

abstract class Delegate implements LoggerInterface
{
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param \Throwable $e
     *
     * @return void
     */
    public function log(\Throwable $e): void
    {
        $this->logger->log($e);
    }
}
