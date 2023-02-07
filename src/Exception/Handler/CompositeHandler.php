<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception\Handler;

use Bic\Foundation\Exception\HandlerInterface;

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
     * @param non-empty-list<HandlerInterface> $handlers
     *
     * @return static
     */
    public static function from(iterable $handlers): self
    {
        return new self([...$handlers]);
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
