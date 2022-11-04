<?php

declare(strict_types=1);

namespace Bic\Foundation\Controller;

use Bic\Foundation\Event;

/**
 * @template TController of object
 */
abstract class ControllerEvent extends Event
{
    /**
     * @param TController $target
     */
    public function __construct(
        public readonly object $target,
    ) {
    }

    /**
     * @return TController
     */
    public function getTarget(): object
    {
        return $this->target;
    }
}
