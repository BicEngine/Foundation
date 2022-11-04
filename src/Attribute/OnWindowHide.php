<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Window\Event\WindowHideEvent;

/**
 * @template-extends OnWindowEvent<WindowHideEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnWindowHide extends OnWindowEvent
{
    public function __construct()
    {
        parent::__construct(WindowHideEvent::class);
    }
}
