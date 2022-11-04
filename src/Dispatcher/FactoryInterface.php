<?php

declare(strict_types=1);

namespace Bic\Foundation\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;

interface FactoryInterface
{
    /**
     * @param object $target
     *
     * @return EventDispatcherInterface
     */
    public function create(object $target): EventDispatcherInterface;
}
