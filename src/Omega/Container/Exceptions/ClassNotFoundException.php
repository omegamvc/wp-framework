<?php

declare(strict_types=1);

namespace Omega\Container\Exceptions;

/**
 * An exception to be thrown when a class cannot be loaded.
 */
class ClassNotFoundException extends AbstractContainerException
{
    /**
     * Creates a new exception instance.
     *
     * @param string $className Arbitrary class name.
     * @return void
     */
    public function __construct(string $className)
    {
        parent::__construct('Class cannot be loaded: ' . $className);
    }
}