<?php

declare(strict_types=1);

namespace Bic\Foundation\Lifecycle;

use Bic\Async\LoopInterface;
use Bic\Foundation\Lifecycle\Event\RenderEvent;
use Bic\Foundation\Lifecycle\Event\UpdateEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

final class MainLoop
{
    /**
     * @var UpdateEvent
     */
    private readonly UpdateEvent $update;

    /**
     * @var RenderEvent
     */
    private readonly RenderEvent $render;

    /**
     * @var Interval
     */
    private readonly Interval $timer;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $this->update = new UpdateEvent(.0);
        $this->render = new RenderEvent(.0);

        $this->timer = new Interval(function (float $delta) {
            $this->update->delta = $delta;
            $this->dispatcher->dispatch($this->update);

            $this->render->delta = $delta;
            $this->dispatcher->dispatch($this->render);
        }, 1 / 60);
    }

    /**
     * Note: This method cannot execute outside a {@see \Fiber}.
     *
     * {@inheritDoc}
     */
    public function run(): void
    {
        $time = \microtime(true);

        while (true) {
            $delta = ($now = \microtime(true)) - $time;

            \Fiber::suspend();
            $this->timer->update($delta);
            \Fiber::suspend();

            $time = $now;
        }
    }
}
