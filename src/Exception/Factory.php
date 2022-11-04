<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception;

use FFI\Env\Runtime;

final class Factory implements HandlerInterface
{
    /**
     * @param bool $ui
     */
    public function __construct(
        public readonly bool $ui = true,
    ) {
    }

    /**
     * @return HandlerInterface
     */
    private function cli(): HandlerInterface
    {
        if (\class_exists(\NunoMaduro\Collision\Writer::class)) {
            return new CollisionHandler();
        }

        return new ConsoleHandler();
    }

    /**
     * @return HandlerInterface|null
     */
    private function ui(): ?HandlerInterface
    {
        if (\PHP_OS_FAMILY === 'Windows' && Runtime::isAvailable()) {
            return new Win32Handler();
        }

        return null;
    }

    /**
     * @return HandlerInterface
     */
    private function instance(): HandlerInterface
    {
        $ui = $this->ui();

        if ($this->ui && $ui !== null) {
            return new CompositeHandler([$this->cli(), $ui]);
        }

        return $this->cli();
    }

    /**
     * {@inheritDoc}
     */
    public function throw(\Throwable $e): void
    {
        $handler = $this->instance();

        $handler->throw($e);
    }
}
