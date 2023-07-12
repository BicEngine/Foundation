<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception;

interface HandlerInterface
{
    public function throw(\Throwable $e): void;
}
