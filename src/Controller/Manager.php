<?php

declare(strict_types=1);

namespace Bic\Foundation\Controller;

use Bic\Foundation\Controller\Event\ControllerHideEvent;
use Bic\Foundation\Controller\Event\ControllerShowEvent;
use Bic\Foundation\Controller\Event\ControllerSwitchEvent;
use Bic\Foundation\Dispatcher\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class Manager implements ManagerInterface
{
    /**
     * @var EventDispatcherInterface|null
     */
    private ?EventDispatcherInterface $current = null;

    /**
     * @var object|null
     */
    private ?object $controller = null;

    /**
     * @param FactoryInterface $instantiator
     * @param ContainerInterface $container
     * @param EventDispatcherInterface|null $dispatcher
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly FactoryInterface $instantiator,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function use(object|string $controller): void
    {
        if ($this->controller !== null) {
            $this->dispatcher?->dispatch(new ControllerHideEvent($this->controller));
        }

        $instance = $this->instance($controller);

        if ($this->controller !== null) {
            $this->dispatcher?->dispatch(new ControllerSwitchEvent($instance, $this->controller));
        }

        $this->current = $this->instantiator->create($instance);
        $this->controller = $instance;

        $this->dispatcher?->dispatch(new ControllerShowEvent($instance));
    }

    /**
     * @template TController of object
     *
     * @param TController|class-string<TController> $controller
     *
     * @return TController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @psalm-suppress all
     */
    private function instance(object|string $controller): object
    {
        if (\is_string($controller)) {
            return $this->container->get($controller);
        }

        return $controller;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $event): object
    {
        if ($this->current === null) {
            return $event;
        }

        return $this->current->dispatch($event);
    }
}
