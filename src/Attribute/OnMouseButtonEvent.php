<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Mouse\ButtonInterface;
use Bic\UI\Mouse\Event\MouseButtonEvent;

/**
 * @template TEvent of MouseButtonEvent
 *
 * @template-extends OnMouseEvent<TEvent>
 */
abstract class OnMouseButtonEvent extends OnMouseEvent
{
    /**
     * @param class-string<TEvent> $event
     */
    public function __construct(
        string $event,
        public readonly ?ButtonInterface $button,
    ) {
        parent::__construct($event);
    }

    /**
     * {@inheritDoc}
     */
    public function match(object $event, object $target): bool
    {
        assert($event instanceof MouseButtonEvent);

        return $this->button === null || $event->button === $this->button;
    }
}
