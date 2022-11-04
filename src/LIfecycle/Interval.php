<?php

declare(strict_types=1);

namespace Bic\Foundation\Lifecycle;

use Bic\Async\TaskInterface;

/**
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Bic\Foundation
 */
final class Interval
{
    /**
     * @var float
     */
    private float $actual = 0.0;

    /**
     * @var bool
     */
    private bool $paused = false;

    /**
     * @param \Closure(float, static):void $handler
     * @param positive-int|float $interval
     */
    public function __construct(
        private readonly \Closure $handler,
        private readonly int|float $interval = 1.0,
    ) {
        if ($this->interval <= 0.0) {
            throw new \InvalidArgumentException('Interval argument cannot be less than 0');
        }

        if (\is_nan($this->interval) || \is_infinite($this->interval)) {
            throw new \InvalidArgumentException('Interval argument cannot be NaN or Inf');
        }
    }

    /**
     * @return void
     */
    public function pause(): void
    {
        if ($this->paused === false) {
            $this->paused = true;
        }
    }

    /**
     * @return void
     */
    public function continue(): void
    {
        if ($this->paused) {
            $this->paused = false;
        }
    }

    /**
     * @param float $delta
     *
     * @return void
     */
    public function update(float $delta): void
    {
        if ($this->paused) {
            return;
        }

        $this->actual += $delta;

        while ($this->actual > $this->interval) {
            ($this->handler)($this->actual, $this);

            $this->actual -= $this->interval;
        }
    }
}
