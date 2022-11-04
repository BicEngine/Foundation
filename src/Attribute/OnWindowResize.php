<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Window\Event\WindowResizeEvent;

/**
 * @template-extends OnWindowEvent<WindowResizeEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnWindowResize extends OnWindowEvent
{
    public function __construct()
    {
        parent::__construct(WindowResizeEvent::class);
    }
}
