<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception;

final class CompositeHandler implements HandlerInterface
{
    /**
     * @param non-empty-array<HandlerInterface> $handlers
     */
    public function __construct(
        private readonly array $handlers,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function throw(\Throwable $e): void
    {
        foreach ($this->handlers as $handler) {
            $handler->throw($e);
        }
    }
}
