<?php

declare(strict_types=1);

namespace Bic\Foundation;

use Bic\Foundation\Exception\Factory;
use Bic\Foundation\Exception\HandlerInterface as ExceptionHandlerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

abstract class Kernel implements KernelInterface
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
            $this->log(new \ErrorException($message, $code, $code, $file, $line));
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
    }

    /**
     * @param \Throwable $e
     * @return void
     * @throws \Exception
     */
    private function log(\Throwable $e): void
    {
        try {
            $logger = new Logger($this->container);
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
        // Path
        $builder->setDefinition(Path::class, (new Definition(Path::class))
            ->setSynthetic(true));

        // Kernel
        $builder->setDefinition(KernelInterface::class, (new Definition(KernelInterface::class))
            ->setSynthetic(true));

        // Container
        $builder->setDefinition(ContainerInterface::class, (new Definition(ContainerInterface::class))
            ->setSynthetic(true));
        $builder->setAlias(SymfonyContainerInterface::class, ContainerInterface::class);
    }

    /**
     * @param Container $container
     * @return void
     */
    private function extendContainerDefinitions(Container $container): void
    {
        $container->set(Path::class, $this->path);
        $container->set(KernelInterface::class, $this);
        $container->set(ContainerInterface::class, $this->container);
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
        //
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
}
