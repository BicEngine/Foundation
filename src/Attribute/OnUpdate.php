<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\Foundation\Lifecycle\Event\UpdateEvent;

/**
 * @template-extends OnEvent<UpdateEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class OnUpdate extends OnEvent
{
    public function __construct()
    {
        parent::__construct(UpdateEvent::class);
    }
}
