<?php

declare(strict_types=1);

namespace Bic\Foundation\DependencyInjection\CompilerPass;

use Bic\Async\LoopInterface;
use Bic\Async\Task;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RunnableCompilerPass implements CompilerPassInterface
{
    /**
     * @var non-empty-string
     */
    public const SERVICE_TAG = 'kernel.runnable';

    /**
     * @var non-empty-string
     */
    public const TASK_REFERENCE = 'kernel.runnable.task(%s:%s)';

    /**
     * @param class-string<LoopInterface> $service
     */
    public function __construct(
        private readonly string $service,
    ) {
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $loop = $container->getDefinition($this->service);

        foreach ($container->findTaggedServiceIds(self::SERVICE_TAG) as $id => $tags) {
            $definitions = $this->createVirtualDefinition($container, $id, $tags);

            foreach ($definitions as $definition) {
                /**
                 * Attaches task service to event loop:
                 *
                 * ```yaml
                 *  Bic\Async\LoopInterface:
                 *    calls:
                 *      - attach: ['@kernel.runnable.task(TAGGED_SERVICE)']
                 * ```
                 */
                $loop->addMethodCall('attach', [new Reference($definition)]);
            }
        }
    }

    /**
     * Creates "virtual" service like a:
     *
     * ```yaml
     *  kernel.runnable.task(TAGGED_SERVICE):
     *    factory: [ 'Bic\Async\Task', 'fromFiber' ]
     *    arguments:
     *      - [ '@TAGGED_SERVICE', 'run' ]
     * ```
     *
     * Which equals:
     *
     * ```php
     *  // Where $service is a '@TAGGED_SERVICE'
     *  // Where $task is a '@kernel.runnable.task(TAGGED_SERVICE)'
     *
     *  $task = Bic\Async\Task::fromFiber(
     *      $service->run(...)
     *  );
     * ```
     *
     * @param ContainerBuilder $container
     * @param class-string $service
     * @param array<array<non-empty-string>> $tags
     *
     * @return array<non-empty-string>
     */
    private function createVirtualDefinition(ContainerBuilder $container, string $service, array $tags): array
    {
        $result = [];

        foreach ($tags as $arguments) {
            if ($arguments === []) {
                throw new \InvalidArgumentException(
                    \sprintf('Service %s must be tagged using "kernel.runnable: [ \'method\' ]"', $service)
                );
            }

            $identifier = \sprintf(self::TASK_REFERENCE, $service, $arguments[0]);

            $definition = (new Definition(Task::class))
                ->setFactory([Task::class, 'fromFiber'])
                ->setArguments([[new Reference($service), $arguments[0]]]);

            $container->setDefinition($identifier, $definition);

            $result[] = $identifier;
        }

        return $result;
    }
}
