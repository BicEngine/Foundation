<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Window\Event\WindowMoveEvent;

/**
 * @template-extends OnWindowEvent<WindowMoveEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnWindowMove extends OnWindowEvent
{
    public function __construct()
    {
        parent::__construct(WindowMoveEvent::class);
    }
}
