<?php

declare(strict_types=1);

namespace Bic\Foundation\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;

final class EventBus implements EventBusInterface
{
    /**
     * @var \SplObjectStorage<EventDispatcherInterface>
     */
    private readonly \SplObjectStorage $dispatchers;

    public function __construct()
    {
        $this->dispatchers = new \SplObjectStorage();
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatchers->attach($dispatcher);
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatchers->detach($dispatcher);
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $event): object
    {
        foreach ($this->dispatchers as $dispatcher) {
            $dispatcher->dispatch($event);
        }

        return $event;
    }
}
