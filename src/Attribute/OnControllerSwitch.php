<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\Foundation\Controller\Event\ControllerSwitchEvent;

/**
 * @template-extends OnControllerEvent<ControllerSwitchEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class OnControllerSwitch extends OnControllerEvent
{
    public function __construct(bool $self = true)
    {
        parent::__construct(ControllerSwitchEvent::class, $self);
    }
}
