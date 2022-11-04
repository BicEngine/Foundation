<?php

declare(strict_types=1);

namespace Bic\Foundation\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;

interface EventBusInterface extends EventDispatcherInterface
{
    /**
     * @param EventDispatcherInterface $dispatcher
     *
     * @return void
     */
    public function attach(EventDispatcherInterface $dispatcher): void;

    /**
     * @param EventDispatcherInterface $dispatcher
     *
     * @return void
     */
    public function detach(EventDispatcherInterface $dispatcher): void;
}
