<?php

declare(strict_types=1);

namespace Bic\Foundation\Attribute;

use Bic\UI\Window\WindowEvent;

/**
 * @template TEvent of WindowEvent
 *
 * @template-extends OnEvent<TEvent>
 */
abstract class OnWindowEvent extends OnEvent
{
}
