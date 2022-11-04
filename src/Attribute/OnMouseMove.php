<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Mouse\Event\MouseMoveEvent;

/**
 * @template-extends OnMouseEvent<MouseMoveEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class OnMouseMove extends OnMouseEvent
{
    public function __construct()
    {
        parent::__construct(MouseMoveEvent::class);
    }
}
