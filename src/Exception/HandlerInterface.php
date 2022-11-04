<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception;

interface HandlerInterface
{
    /**
     * @param \Throwable $e
     * @return int
     */
    public function throw(\Throwable $e): void;
}
