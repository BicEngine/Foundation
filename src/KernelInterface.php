<?php

declare(strict_types=1);

namespace Bic\Foundation;

use Bic\Foundation\Exception\HandlerInterface as ExceptionHandlerInterface;
use Psr\Container\ContainerInterface;

interface KernelInterface extends ExceptionHandlerInterface, ContainerInterface
{
    /**
     * @return void
     */
    public function run(): void;

    /**
     * @return void
     */
    public function stop(): void;
}
