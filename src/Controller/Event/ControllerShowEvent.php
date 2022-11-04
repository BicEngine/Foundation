<?php

declare(strict_types=1);

namespace Bic\Foundation\Controller\Event;

use Bic\Foundation\Controller\ControllerEvent;

/**
 * @template TController of object
 *
 * @template-extends ControllerEvent<TController>
 */
final class ControllerShowEvent extends ControllerEvent
{
}
