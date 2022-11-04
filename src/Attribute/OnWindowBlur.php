<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Window\Event\WindowBlurEvent;

/**
 * @template-extends OnWindowEvent<WindowBlurEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnWindowBlur extends OnWindowEvent
{
    public function __construct()
    {
        parent::__construct(WindowBlurEvent::class);
    }
}
