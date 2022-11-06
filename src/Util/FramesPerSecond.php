<?php

declare(strict_types=1);

namespace Bic\Foundation\Util;

final class FramesPerSecond
{
    /**
     * @var float
     */
    private float $delta = 1.0;

    /**
     * @var int
     */
    private int $frames = 1;

    /**
     * @var int
     */
    private readonly int $stat2;

    /**
     * @param int $stat The number of frames for which frame statistics are built.
     */
    public function __construct(
        private readonly int $stat = 10,
    ) {
        $this->stat2 = $this->stat * 2;
    }

    /**
     * @return float
     */
    public function getCurrent(): float
    {
        $delta = $this->delta / $this->frames;

        return 1 / $delta;
    }

    /**
     * @param float $delta
     *
     * @return void
     */
    public function update(float $delta): void
    {
        $this->delta += $delta;
        ++$this->frames;

        // Reset statistics
        if ($this->frames >= $this->stat2) {
            $this->frames = $this->stat;
            $this->delta *= .5;
        }
    }
}
