<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Mouse\ButtonInterface;
use Bic\UI\Mouse\Event\MouseUpEvent;

/**
 * @template-extends OnMouseButtonEvent<MouseUpEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class OnMouseButtonUp extends OnMouseButtonEvent
{
    /**
     * @param ButtonInterface|null $button
     */
    public function __construct(
        ?ButtonInterface $button = null,
    ) {
        parent::__construct(MouseUpEvent::class, $button);
    }
}
