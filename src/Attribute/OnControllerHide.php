<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\Foundation\Controller\Event\ControllerHideEvent;

/**
 * @template-extends OnControllerEvent<ControllerHideEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class OnControllerHide extends OnControllerEvent
{
    public function __construct(bool $self = true)
    {
        parent::__construct(ControllerHideEvent::class, $self);
    }
}
