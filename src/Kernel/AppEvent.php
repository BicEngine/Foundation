<?php

declare(strict_types=1);

namespace Bic\Foundation\Kernel;

use Bic\Foundation\Event;
use Bic\Foundation\KernelInterface;

abstract class AppEvent extends Event
{
    /**
     * @param KernelInterface $target
     */
    public function __construct(
        public readonly KernelInterface $target,
    ) {
    }
}

