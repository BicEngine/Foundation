<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Window\Event\WindowCloseEvent;

/**
 * @template-extends OnWindowEvent<WindowCloseEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnWindowClose extends OnWindowEvent
{
    public function __construct()
    {
        parent::__construct(WindowCloseEvent::class);
    }
}
