<?php

declare(strict_types=1);

namespace Omega\Container\Exceptions;

use RuntimeException;

/**
 * An exception class intended to be extended by all other container exceptions.
 */
abstract class AbstractContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
