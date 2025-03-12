<?php

declare(strict_types=1);

namespace Omega\Container\Exceptions;

/**
 * An exception to be thrown when a dependency is recursive.
 */
class RecursiveDependencyException extends AbstractContainerException
{
    /**
     * Creates a new exception instance.
     *
     * @param string $identifier Arbitrary identifier.
     * @return void
     */
    public function __construct(string $identifier)
    {
        parent::__construct('Identifier is being resolved recursively: ' . $identifier);
    }
}