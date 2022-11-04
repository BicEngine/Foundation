<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

/**
 * @template TEvent of object
 */
interface AttributeInterface
{
    /**
     * @return class-string<TEvent>
     */
    public function getEvent(): string;

    /**
     * @param object $event
     * @param object $target
     *
     * @return bool
     */
    public function match(object $event, object $target): bool;
}
