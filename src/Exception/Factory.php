<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception;

use Bic\Foundation\Exception\Handler\CollisionHandler;
use Bic\Foundation\Exception\Handler\CompositeHandler;
use Bic\Foundation\Exception\Handler\ConsoleHandler;
use Bic\Foundation\Exception\Handler\Win32Handler;
use FFI\Env\Runtime;

final class Factory implements HandlerInterface
{
    public function __construct(
        public readonly bool $ui = true,
    ) {
    }

    private function cli(): HandlerInterface
    {
        if (\class_exists(\NunoMaduro\Collision\Writer::class)) {
            return new CollisionHandler();
        }

        return new ConsoleHandler();
    }

    private function ui(): ?HandlerInterface
    {
        if (\PHP_OS_FAMILY === 'Windows' && Runtime::isAvailable()) {
            return new Win32Handler();
        }

        return null;
    }

    private function instance(): HandlerInterface
    {
        $ui = $this->ui();

        if ($this->ui && $ui !== null) {
            return new CompositeHandler([$this->cli(), $ui]);
        }

        return $this->cli();
    }

    public function throw(\Throwable $e): void
    {
        $handler = $this->instance();

        $handler->throw($e);
    }
}
