<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\Foundation\Kernel\Event\AppExitEvent;

/**
 * @template-extends OnEvent<AppExitEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnAppExit extends OnEvent
{
    public function __construct()
    {
        parent::__construct(AppExitEvent::class);
    }
}
