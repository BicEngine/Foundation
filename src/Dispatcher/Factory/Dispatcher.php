<?php

declare(strict_types=1);

namespace Bic\Foundation\Dispatcher\Factory;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @template TTarget of object
 *
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Bic\Foundation\Dispatcher
 */
final class Dispatcher implements EventDispatcherInterface
{
    /**
     * @param TTarget $target
     * @param array<class-string, list<Action>> $actions
     */
    public function __construct(
        private readonly object $target,
        private readonly array $actions,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $event): object
    {
        if ($this->target instanceof EventDispatcherInterface) {
            $this->target->dispatch($event);
        }

        if (!isset($this->actions[$event::class])) {
            return $event;
        }

        foreach ($this->actions[$event::class] as $action) {
            if ($action->attribute->match($event, $this->target)) {
                ($action->method)($event);
            }
        }

        return $event;
    }
}
