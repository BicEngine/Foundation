<?php

declare(strict_types=1);

namespace Bic\Foundation\Controller\Event;

use Bic\Foundation\Controller\ControllerEvent;

/**
 * @template TController of object
 * @template TPrevious of object
 *
 * @template-extends ControllerEvent<TController>
 */
final class ControllerSwitchEvent extends ControllerEvent
{
    /**
     * @param TController $target
     * @param TPrevious $previous
     */
    public function __construct(
        object $target,
        public readonly object $previous,
    ) {
        parent::__construct($target);
    }
}
