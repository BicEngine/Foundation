<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\Foundation\Controller\Event\ControllerShowEvent;

/**
 * @template-extends OnControllerEvent<ControllerShowEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class OnControllerShow extends OnControllerEvent
{
    public function __construct(bool $self = true)
    {
        parent::__construct(ControllerShowEvent::class, $self);
    }
}
