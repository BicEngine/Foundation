<?php

declare(strict_types=1);

namespace Bic\Foundation;

use Bic\Async\LoopInterface;
use Bic\Foundation\Exception\Factory;
use Bic\Foundation\Exception\HandlerInterface as ExceptionHandlerInterface;
use Bic\Foundation\DependencyInjection\CompilerPass\EventListenerCompilerPass;
use Bic\Foundation\DependencyInjection\CompilerPass\RunnableCompilerPass;
use Bic\Foundation\Dispatcher\FactoryInterface;
use Bic\Foundation\Kernel\Event\AppLaunchEvent;
use Bic\Foundation\Kernel\Event\AppExitEvent;
use Bic\Foundation\Kernel\ErrorLogger;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Kernel implements KernelInterface
{
    /**
     * @var int
     */
    protected const ERROR_LEVEL = \E_ALL;

    /**
     * @var Path
     */
    private readonly Path $path;

    /**
     * @var Container
     */
    private readonly Container $container;

    /**
     * @var LoopInterface
     */
    protected readonly LoopInterface $loop;

    /**
     * @var EventDispatcherInterface
     */
    protected readonly EventDispatcherInterface $events;

    /**
     * @psalm-taint-sink file $root
     *
     * @param bool $debug
     * @param non-empty-string|Path $root
     * @param ExceptionHandlerInterface $exception
     * @throws \Exception
     */
    public function __construct(
        string|Path $root,
        protected readonly bool $debug = false,
        protected readonly ExceptionHandlerInterface $exception = new Factory(),
    ) {
        $this->bootPath($root);
        $this->listenErrors();

        try {
            $this->container = $this->getCachedContainer(
                $this->getContainerPathname(),
                $this->getContainerClass(),
            );

            $this->extendContainerDefinitions($this->container);

            $this->loop = $this->container->get(LoopInterface::class);
            $this->events = $this->container->get(EventDispatcherInterface::class);
        } catch (\Throwable $e) {
            $this->throw($e);
        }
    }

    /**
     * @psalm-taint-sink file $root
     * @param non-empty-string|Path $root
     *
     * @return void
     */
    private function bootPath(string|Path $root): void
    {
        if (\is_string($root)) {
            $root = new Path($root);
        }

        $this->path = $root;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function listenErrors(): void
    {
        \set_error_handler(function (int $code, string $message, string $file, int $line): void {
            $this->throw(new \ErrorException($message, $code, $code, $file, $line));
        }, static::ERROR_LEVEL);
    }

    /**
     * @param \Throwable $e
     * @return int
     * @throws \Exception
     */
    public function throw(\Throwable $e): void
    {
        $this->log($e);

        $this->exception->throw($e);

        $this->stop();
    }

    /**
     * @param \Throwable $e
     * @return void
     * @throws \Exception
     */
    private function log(\Throwable $e): void
    {
        try {
            $logger = new ErrorLogger($this->container);
            $logger->log($e);
        } finally {
            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    /**
     * @template TEntryObject of object
     *
     * @param class-string $id
     * @return TEntryObject
     *
     * @throws \Exception
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function get(string $id): object
    {
        return $this->container->get($id);
    }

    /**
     * @param non-empty-string $pathname
     * @param class-string<Container> $class
     * @return Container
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     */
    private function getCachedContainer(string $pathname, string $class): Container
    {
        if ($this->debug || !\is_file($pathname)) {
            $dumper = new PhpDumper($this->createContainer());

            if (!\is_dir(\dirname($pathname))) {
                \mkdir(\dirname($pathname), recursive: true);
            }

            /** @var string $result */
            $result = $dumper->dump(['class' => $class]);

            \file_put_contents($pathname, $result);
        }

        require $pathname;

        return new $class();
    }

    /**
     * @return ContainerBuilder
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     */
    private function createContainer(): ContainerBuilder
    {
        $builder = new ContainerBuilder();

        $this->extendContainerBuilderParameters($builder);
        $this->extendContainerBuilderDefinitions($builder);
        $this->extendContainerBuilderConfigs($builder);
        $this->extendContainerBuilderCompilerPass($builder);

        $builder->compile();

        return $builder;
    }

    /**
     * @param ContainerBuilder $builder
     * @return void
     */
    private function extendContainerBuilderParameters(ContainerBuilder $builder): void
    {
        $builder->setParameter('app.debug', $this->debug);
        $builder->setParameter('app.environment', $this->getEnvironment());
        $builder->setParameter('app.date', \date('Y-m-d'));

        $builder->setParameter('dir.root', $this->path->root);
        $builder->setParameter('dir.app', $this->path->app);
        $builder->setParameter('dir.config', $this->path->config);
        $builder->setParameter('dir.storage', $this->path->storage);
        $builder->setParameter('dir.vendor', $this->path->vendor);
    }

    /**
     * @return string
     */
    private function getEnvironment(): string
    {
        return \strtolower(\PHP_OS_FAMILY);
    }

    /**
     * @param ContainerBuilder $builder
     * @return void
     */
    private function extendContainerBuilderDefinitions(ContainerBuilder $builder): void
    {
        $builder->setDefinition(ContainerInterface::class, (new Definition(self::class))->setSynthetic(true));

        $builder->setAlias(self::class, ContainerInterface::class);
        $builder->setAlias(KernelInterface::class, ContainerInterface::class);

        if (static::class !== self::class) {
            $builder->setAlias(static::class, ContainerInterface::class);
        }
    }

    /**
     * @param ContainerBuilder $builder
     * @return void
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     */
    private function extendContainerBuilderConfigs(ContainerBuilder $builder): void
    {
        $loader = new YamlFileLoader($builder, new FileLocator(
            $this->getConfigDirectories(),
        ), $this->getEnvironment());

        $loader->import(__DIR__ . '/../resources/config/*.yaml');

        foreach ($this->getConfigDirectories() as $directory) {
            $loader->import($directory . '/*.yaml');
            $loader->import($directory . '/*/*.yaml');
        }
    }

    /**
     * @return array<non-empty-string>
     */
    protected function getConfigDirectories(): array
    {
        if (!\is_dir($this->path->config)) {
            return [];
        }

        return [$this->path->config];
    }

    /**
     * @param ContainerBuilder $builder
     * @return void
     */
    private function extendContainerBuilderCompilerPass(ContainerBuilder $builder): void
    {
        $builder->addCompilerPass(new RunnableCompilerPass(
            LoopInterface::class,
        ));

        $builder->addCompilerPass(new EventListenerCompilerPass(
            EventDispatcherInterface::class,
            FactoryInterface::class,
        ));
    }

    /**
     * @return non-empty-string
     */
    private function getContainerPathname(): string
    {
        return $this->path->storage($this->getContainerClass() . '.php');
    }

    /**
     * @return class-string<Container>
     */
    private function getContainerClass(): string
    {
        return \ucfirst($this->getEnvironment()) . 'AppContainer';
    }

    /**
     * @param Container $container
     * @return void
     */
    private function extendContainerDefinitions(Container $container): void
    {
        $container->set(ContainerInterface::class, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function run(): void
    {
        $this->events->dispatch(new AppLaunchEvent($this));

        try {
            $this->loop->start();
        } catch (\Throwable $e) {
            $this->throw($e);
        } finally {
            $this->events->dispatch(new AppExitEvent($this));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stop(): void
    {
        try {
            $this->loop->stop();
        } finally {
            return;
        }
    }
}
