<?php

declare(strict_types=1);

namespace Omega\Container\Exceptions;

use ReflectionParameter;

/**
 * An exception to be thrown when a dependency cannot be resolved.
 */
class DependencyResolutionException extends AbstractContainerException
{
    /**
     * Creates a new exception instance.
     *
     * @param ReflectionParameter $dependency Arbitrary dependency.
     * @return void
     */
    public function __construct(ReflectionParameter $dependency)
    {
        $class = $dependency->getDeclaringClass();
        $method = $dependency->getDeclaringFunction()->getName();

        if (!is_null($class)) {
            $method = $class->getName() . '::' . $method;
        }

        parent::__construct('Unresolved dependency: ' . $dependency->getName() . ' in ' . $method);
    }
}
