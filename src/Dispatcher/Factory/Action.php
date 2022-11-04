<?php

declare(strict_types=1);

namespace Bic\Foundation\Dispatcher\Factory;

use Bic\Foundation\Attribute\AttributeInterface;

/**
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Bic\Foundation\Dispatcher
 */
final class Action
{
    /**
     * @param \Closure $method
     * @param AttributeInterface $attribute
     */
    public function __construct(
        public readonly \Closure $method,
        public readonly AttributeInterface $attribute,
    ) {
    }
}
