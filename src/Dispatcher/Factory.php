<?php

declare(strict_types=1);

namespace Bic\Foundation\Dispatcher;

use Bic\Foundation\Dispatcher\Factory\Context;
use Bic\Foundation\Dispatcher\Factory\Dispatcher;

final class Factory implements FactoryInterface
{
    /**
     * @var array<class-string, Context>
     */
    private array $contexts = [];

    /**
     * @template TTarget of object
     *
     * @param TTarget $target
     *
     * @return Dispatcher<TTarget>
     * @throws \ReflectionException
     */
    public function create(object $target): Dispatcher
    {
        $context = ($this->contexts[$target::class] ??= new Context($target::class));

        return new Dispatcher($target, $context->getMethods($target));
    }
}
