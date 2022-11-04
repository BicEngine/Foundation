<?php

declare(strict_types=1);

namespace Bic\Foundation\Dispatcher\Factory;

use Bic\Foundation\Attribute\AttributeInterface;

/**
 * @template TTarget of object
 *
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Bic\Foundation\Dispatcher
 */
final class Context
{
    /**
     * @var array<class-string, list<ActionPrototype>>
     */
    private array $actions = [];

    /**
     * @param class-string<TTarget> $class
     * @throws \ReflectionException
     */
    public function __construct(
        private readonly string $class,
    ) {
        foreach ((new \ReflectionClass($this->class))->getMethods() as $method) {
            foreach ($this->getAttributes($method) as $event => $attribute) {
                $this->actions[$event][] = new ActionPrototype($method, $attribute);
            }
        }
    }

    /**
     * @param \ReflectionMethod $method
     *
     * @return iterable<class-string, object>
     */
    private function getAttributes(\ReflectionMethod $method): iterable
    {
        $events = [];

        foreach ($method->getAttributes(AttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            /** @var AttributeInterface $instance */
            $instance = $attribute->newInstance();

            $events[$instance->getEvent()] = $instance;
        }

        return $events;
    }

    /**
     * @param TTarget $context
     *
     * @return array<class-string, list<Action>>
     */
    public function getMethods(object $context): array
    {
        $result = [];

        foreach ($this->actions as $event => $methods) {
            foreach ($methods as $action) {
                $result[$event][] = $action->create($context);
            }
        }

        return $result;
    }
}
