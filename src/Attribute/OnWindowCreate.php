<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Window\Event\WindowCreateEvent;

/**
 * @template-extends OnWindowEvent<WindowCreateEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnWindowCreate extends OnWindowEvent
{
    public function __construct()
    {
        parent::__construct(WindowCreateEvent::class);
    }
}
