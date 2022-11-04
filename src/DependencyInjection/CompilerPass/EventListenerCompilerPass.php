<?php

declare(strict_types=1);

namespace Bic\Foundation\DependencyInjection\CompilerPass;

use Bic\Foundation\Dispatcher\EventBusInterface;
use Bic\Foundation\Dispatcher\Factory\Dispatcher;
use Bic\Foundation\Dispatcher\FactoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * All services tagged by "kernel.listener" are attached to the system event bus
 * and receive all events sent in the system.
 *
 * It also must implement {@see EventDispatcherInterface}.
 *
 * ```php
 *  class ExampleListener implements EventDispatcherInterface
 *  {
 *      public function dispatch(object $event): void
 *      {
 *          var_dump($event);
 *      }
 *  }
 * ```
 *
 * ```yaml
 *  ExampleListener:
 *    tags: [ 'kernel.listener' ]
 * ```
 */
final class EventListenerCompilerPass implements CompilerPassInterface
{
    /**
     * @var non-empty-string
     */
    public const SERVICE_TAG = 'kernel.listener';

    /**
     * @var non-empty-string
     */
    public const LISTENER_REFERENCE = 'kernel.listener(%s)';

    /**
     * @param class-string<EventBusInterface> $bus
     * @param class-string<FactoryInterface> $factory
     */
    public function __construct(
        private readonly string $bus,
        private readonly string $factory,
    ) {
    }

    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $bus = $container->getDefinition($this->bus);

        foreach ($container->findTaggedServiceIds(self::SERVICE_TAG) as $id => $_) {
            $identifier = \sprintf(self::LISTENER_REFERENCE, $id);

            $definition = (new Definition(Dispatcher::class))
                ->setFactory([ new Reference($this->factory), 'create' ])
                ->setArguments([ new Reference($id) ]);

            $container->setDefinition($identifier, $definition);

            $bus->addMethodCall('attach', [new Reference($identifier)]);
        }
    }
}
