<?php

declare(strict_types=1);

namespace Omega\Container\Exceptions;

/**
 * An exception to be thrown when an identifier cannot be instantiated.
 */
class NotInstantiableException extends AbstractContainerException
{
    /**
     * Creates a new exception instance.
     *
     * @param string $className Arbitrary class name.
     * @return void
     */
    public function __construct(string $className)
    {
        parent::__construct('Cannot instantiate: ' . $className);
    }
}
