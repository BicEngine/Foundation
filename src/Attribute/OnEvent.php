<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

/**
 * @template TEvent of object
 *
 * @template-implements AttributeInterface<TEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class OnEvent implements AttributeInterface
{
    /**
     * @param class-string<TEvent> $event
     */
    public function __construct(
        private readonly string $event,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * {@inheritDoc}
     */
    public function match(object $event, object $target): bool
    {
        return true;
    }
}
