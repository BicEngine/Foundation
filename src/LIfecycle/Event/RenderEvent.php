<?php

declare(strict_types=1);

namespace Bic\Foundation\Lifecycle\Event;

use Bic\Foundation\Lifecycle\LifecycleEvent;
use Bic\UI\Window\WindowInterface;

final class RenderEvent extends LifecycleEvent
{
    public function __construct(
        public readonly WindowInterface $window,
        float $delta,
    ) {
        parent::__construct($delta);
    }
}
