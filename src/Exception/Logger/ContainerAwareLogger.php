<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception\Logger;

use Bic\Foundation\Exception\LoggerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ContainerAwareLogger extends Delegate
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
        parent::__construct(new LazyInitializedLogger(
            $this->initializer(...),
        ));
    }

    /**
     * @return LoggerInterface|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function initializer(): ?LoggerInterface
    {
        if ($this->container->has(LoggerInterface::class)) {
            return $this->container->get(LoggerInterface::class);
        }

        return null;
    }
}
