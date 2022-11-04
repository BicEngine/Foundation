<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Keyboard\Event\KeyDownEvent;
use Bic\UI\Keyboard\Event\KeyUpEvent;
use Bic\UI\Keyboard\KeyInterface;

/**
 * @template-extends OnKeyEvent<KeyDownEvent>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class OnKeyUp extends OnKeyEvent
{
    /**
     * @param KeyInterface|null $key
     */
    public function __construct(
        ?KeyInterface $key = null,
    ) {
        parent::__construct(KeyUpEvent::class, $key);
    }
}
