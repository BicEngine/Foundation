<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Keyboard\KeyEvent;
use Bic\UI\Keyboard\KeyInterface;

/**
 * @template TEvent of KeyEvent
 *
 * @template-extends OnEvent<TEvent>
 */
abstract class OnKeyEvent extends OnEvent
{
    /**
     * @param class-string<TEvent> $event
     * @param KeyInterface|null $key
     */
    public function __construct(
        string $event,
        public readonly ?KeyInterface $key,
    ) {
        parent::__construct($event);
    }

    /**
     * {@inheritDoc}
     */
    public function match(object $event, object $target): bool
    {
        assert($event instanceof KeyEvent);

        return $this->key === null || $event->key === $this->key;
    }
}
