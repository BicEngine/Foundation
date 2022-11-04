<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Window\Event\WindowShowEvent;

/**
 * @template-extends OnWindowEvent<WindowShowEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnWindowShow extends OnWindowEvent
{
    public function __construct()
    {
        parent::__construct(WindowShowEvent::class);
    }
}
