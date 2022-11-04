<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Mouse\Event\MouseWheelEvent;
use Bic\UI\Mouse\Wheel;

/**
 * @template-extends OnMouseEvent<MouseWheelEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class OnMouseWheel extends OnMouseEvent
{
    /**
     * @param Wheel|null $wheel
     */
    public function __construct(
        public readonly ?Wheel $wheel = null,
    ) {
        parent::__construct(MouseWheelEvent::class);
    }

    /**
     * {@inheritDoc}
     */
    public function match(object $event, object $target): bool
    {
        assert($event instanceof MouseWheelEvent);

        return $this->wheel === null || $event->wheel === $this->wheel;
    }
}
