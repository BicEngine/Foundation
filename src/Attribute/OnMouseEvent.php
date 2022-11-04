<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Mouse\MouseEvent;

/**
 * @template TEvent of MouseEvent
 *
 * @template-extends OnEvent<TEvent>
 */
abstract class OnMouseEvent extends OnEvent
{
}
