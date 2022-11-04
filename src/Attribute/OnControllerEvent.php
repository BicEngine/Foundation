<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\Foundation\Controller\ControllerEvent;

/**
 * @template TEvent of ControllerEvent
 *
 * @template-extends OnEvent<ControllerEvent>
 */
abstract class OnControllerEvent extends OnEvent
{
    /**
     * @param class-string<TEvent> $event
     * @param bool $self Applied to selected controller only
     */
    public function __construct(
        string $event,
        public readonly bool $self,
    ) {
        parent::__construct($event);
    }

    /**
     * {@inheritDoc}
     */
    public function match(object $event, object $target): bool
    {
        assert($event instanceof ControllerEvent);

        return $this->self === false || $target === $event->target;
    }
}
