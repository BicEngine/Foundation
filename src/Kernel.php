<?php

declare(strict_types=1);

namespace Bic\Foundation;

use Dotenv\Dotenv;
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
    public readonly Path $path;

    public readonly Container $container;

    /**
     * @psalm-taint-sink file $root
     * @param non-empty-string|Path $root
     * @throws \Exception
     */
    public function __construct(
        string|Path $root,
        public readonly string $environment,
        public readonly bool $debug,
    ) {
        $this->bootPath($root);

        $this->container = $this->getCachedContainer(
            pathname: $this->getContainerPathname(),
            class: $this->getContainerClass(),
        );

        $this->extendContainerDefinitions($this->container);
    }

    /**
     * @psalm-taint-sink file $directory
     * @param non-empty-string $directory
     * @return class-string<static>
     */
    public static function dotenv(string $directory): string
    {
        if (\is_file('.env')) {
            $dotenv = Dotenv::createImmutable($directory);
            $dotenv->load();
        }

        return static::class;
    }

    /**
     * @psalm-taint-sink file $root
     * @param non-empty-string|Path $root
     */
    private function bootPath(string|Path $root): void
    {
        if (\is_string($root)) {
            $root = new Path($root);
        }

        $this->path = $root;
    }

    /**
     * @param non-empty-string $id
     */
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    /**
     * @template TEntryObject of object
     *
     * @param class-string<TEntryObject>|non-empty-string $id
     *
     * @return ($id is class-string<TEntryObject> ? TEntryObject : object)
     *
     * @throws \Exception
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function get(string $id): object
    {
        if (!isset($this->container)) {
            if ($id === static::class || $id === self::class) {
                return $this;
            }

            $message = 'Could not fetch service "%s" from non-initialized container';
            throw new \UnderflowException(\sprintf($message, $id));
        }

        return $this->container->get($id);
    }

    /**
     * @param non-empty-string $pathname
     * @param class-string<Container> $class
     *
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     */
    private function getCachedContainer(string $pathname, string $class): Container
    {
        if ($this->debug || !\is_file($pathname)) {
            $dumper = new PhpDumper($this->createContainer());

            if (!@\mkdir($directory = \dirname($pathname), recursive: true)
                && !\is_dir($directory)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $directory));
            }

            /** @var string $result */
            $result = $dumper->dump(['class' => $class]);

            \file_put_contents($pathname, $result);
        }

        require_once $pathname;

        return new $class();
    }

    /**
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

    protected function extendContainerBuilderParameters(ContainerBuilder $builder): void
    {
        $builder->setParameter('kernel.debug', $this->debug);
        $builder->setParameter('kernel.os', \strtolower(\PHP_OS_FAMILY));
        $builder->setParameter('kernel.bits', \PHP_INT_SIZE === 8 ? 64 : 32);
        $builder->setParameter('kernel.environment', $this->getEnvironment());
        $builder->setParameter('kernel.date', \date('Y-m-d'));

        $builder->setParameter('dir.root', $this->path->root);
        $builder->setParameter('dir.app', $this->path->app);
        $builder->setParameter('dir.config', $this->path->config);
        $builder->setParameter('dir.storage', $this->path->storage);
        $builder->setParameter('dir.vendor', $this->path->vendor);
    }

    /**
     * @return non-empty-string
     */
    private function getEnvironment(): string
    {
        return $this->environment;
    }

    protected function extendContainerBuilderDefinitions(ContainerBuilder $builder): void
    {
        // Path
        $builder->setDefinition(Path::class, (new Definition(Path::class))
            ->setSynthetic(true));

        // Kernel
        $builder->setDefinition(KernelInterface::class, (new Definition(KernelInterface::class))
            ->setSynthetic(true));

        $builder->setAlias(self::class, KernelInterface::class);
        $builder->setAlias(static::class, KernelInterface::class);

        // Container
        $builder->setDefinition(ContainerInterface::class, (new Definition(ContainerInterface::class))
            ->setSynthetic(true));
        $builder->setAlias(SymfonyContainerInterface::class, ContainerInterface::class);
    }

    protected function extendContainerDefinitions(Container $container): void
    {
        $container->set(Path::class, $this->path);
        $container->set(KernelInterface::class, $this);
        $container->set(ContainerInterface::class, $this->container);
    }

    /**
     * @throws FileLoaderImportCircularReferenceException
     * @throws LoaderLoadException
     */
    protected function extendContainerBuilderConfigs(ContainerBuilder $builder): void
    {
        $loader = new YamlFileLoader($builder, new FileLocator(
            $this->getConfigDirectories(),
        ), $this->getEnvironment());

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

    protected function extendContainerBuilderCompilerPass(ContainerBuilder $builder): void
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
