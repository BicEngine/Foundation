<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Mouse\ButtonInterface;
use Bic\UI\Mouse\Event\MouseDownEvent;

/**
 * @template-extends OnMouseButtonEvent<MouseDownEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class OnMouseButtonDown extends OnMouseButtonEvent
{
    /**
     * @param ButtonInterface|null $button
     */
    public function __construct(
        ?ButtonInterface $button = null,
    ) {
        parent::__construct(MouseDownEvent::class, $button);
    }
}
