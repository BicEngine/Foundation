<?php

declare(strict_types=1);

namespace Bic\Foundation\Dispatcher\Factory;

use Bic\Foundation\Attribute\AttributeInterface;

/**
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Bic\Foundation\Dispatcher
 */
final class ActionPrototype
{
    /**
     * @param \ReflectionMethod $method
     * @param AttributeInterface $attribute
     */
    public function __construct(
        public readonly \ReflectionMethod $method,
        public readonly AttributeInterface $attribute,
    ) {
    }

    /**
     * @param object $context
     *
     * @return Action
     */
    public function create(object $context): Action
    {
        return new Action($this->method->getClosure($context), $this->attribute);
    }
}
