<?php

declare(strict_types=1);

namespace Bic\Foundation\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;

interface ManagerInterface extends EventDispatcherInterface
{
    /**
     * @param class-string|object $controller
     *
     * @return void
     */
    public function use(string|object $controller): void;
}
