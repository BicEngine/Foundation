<?php

declare(strict_types=1);

namespace Bic\Foundation;

use Bic\Foundation\Exception\HandlerInterface;
use Bic\Foundation\RunnableInterface;
use Psr\Container\ContainerInterface;

interface KernelInterface extends RunnableInterface, HandlerInterface, ContainerInterface
{
}
