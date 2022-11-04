<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\Foundation\Kernel\Event\AppLaunchEvent;

/**
 * @template-extends OnEvent<AppLaunchEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnAppLaunch extends OnEvent
{
    public function __construct()
    {
        parent::__construct(AppLaunchEvent::class);
    }
}
