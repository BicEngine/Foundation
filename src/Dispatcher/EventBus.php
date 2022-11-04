<?php

declare(strict_types=1);

namespace Bic\Foundation\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;

final class EventBus implements EventBusInterface
{
    /**
     * Note: When using a {@see \SplObjectStorage}, instead of an array, you
     * can get a PHP bug in which iteration over the object may not occur until
     * the end in the {@see dispatch()} method:
     *
     * ```php
     *  foreach ($this->dispatchers as $i => $dispatcher) {
     *     echo $i . ': ' . $event::class . "\n"; // <<< dump
     *     $dispatcher->dispatch($event);
     *  }
     *
     *  // array
     *  - 0: Event#1
     *  - 1: Event#1
     *  - 0: Event#2
     *  - 1: Event#2
     *
     *  // SplObjectStorage
     *  - 0: Event#1
     *  - 0: Event#2
     *  - 1: Event#2
     * ```
     *
     * This is most likely due to resetting the iterator state inside the
     * {@see SplObjectStorage}, but we should find out why this happens, because
     * dispatchers list does not change during iteration.
     *
     * @var array<EventDispatcherInterface>
     */
    private array $dispatchers = [];

    /**
     * {@inheritDoc}
     */
    public function attach(EventDispatcherInterface $dispatcher): void
    {
        if (\in_array($dispatcher, $this->dispatchers, true)) {
            return;
        }

        $this->dispatchers[] = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventDispatcherInterface $dispatcher): void
    {
        foreach ($this->dispatchers as $index => $actual) {
            if ($dispatcher === $actual) {
                unset($this->dispatchers[$index]);
                return;
            }
        }
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
