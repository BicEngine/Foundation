<?php

declare(strict_types=1);

namespace Bic\Foundation\Lifecycle;

use Bic\Foundation\Event;

abstract class LifecycleEvent extends Event
{
    /**
     * @param float $delta
     */
    public function __construct(
        public float $delta,
    ) {
    }
}
