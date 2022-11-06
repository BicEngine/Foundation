<?php

declare(strict_types=1);

namespace Bic\Foundation\Lifecycle;

use Bic\Foundation\Attribute\OnWindowClose;
use Bic\Foundation\Attribute\OnWindowCreate;
use Bic\Foundation\Lifecycle\Event\RenderEvent;
use Bic\Foundation\Lifecycle\Event\UpdateEvent;
use Bic\UI\Window\Event\WindowCloseEvent;
use Bic\UI\Window\Event\WindowCreateEvent;
use Bic\UI\Window\WindowInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class MainLoop
{
    /**
     * @var \WeakMap<WindowInterface, RenderEvent>
     */
    private readonly \WeakMap $windows;

    /**
     * @var UpdateEvent
     */
    private readonly UpdateEvent $update;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {
        $this->update = new UpdateEvent(.0);
        $this->windows = new \WeakMap();
    }

    #[OnWindowCreate]
    protected function onWindowCreate(WindowCreateEvent $e): void
    {
        $this->windows[$e->target] = new RenderEvent($e->target, .0);
    }

    #[OnWindowClose]
    protected function onWindowClose(WindowCloseEvent $e): void
    {
        unset($this->windows[$e->target]);
    }

    /**
     * Note: This method cannot execute outside a {@see \Fiber}.
     */
    public function run(): void
    {
        $time = \microtime(true);

        while (true) {
            $delta = ($now = \microtime(true)) - $time;

            \Fiber::suspend();
            $this->update->delta = $delta;
            $this->dispatcher->dispatch($this->update);
            \Fiber::suspend();

            foreach ($this->windows as $render) {
                $render->delta = $delta;
                $this->dispatcher->dispatch($render);
                \Fiber::suspend();
            }

            $time = $now;
        }
    }
}
