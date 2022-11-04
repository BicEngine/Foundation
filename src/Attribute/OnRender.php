<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\Foundation\Lifecycle\Event\RenderEvent;

/**
 * @template-extends OnEvent<RenderEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnRender extends OnEvent
{
    public function __construct()
    {
        parent::__construct(RenderEvent::class);
    }
}
