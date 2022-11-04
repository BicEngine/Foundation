<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Window\Event\WindowFocusEvent;

/**
 * @template-extends OnWindowEvent<WindowFocusEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnWindowFocus extends OnWindowEvent
{
    public function __construct()
    {
        parent::__construct(WindowFocusEvent::class);
    }
}
