<?php

declare(strict_types=1);

namespace Omega\Container;

/**
 * Represents a dependency injection container.
 */
interface ContainerInterface
{
    /**
     * Binds a class to the given identifier.
     *
     * This allows an instance of the class to later be resolved with the identifier.
     *
     * @param string $identifier Arbitrary identifier.
     * @param string $className Name of the class to bind to the identifier.
     * @return void
     */
    public function bindClass(string $identifier, string $className): void;

    /**
     * Binds an arbitrary value to the given identifier.
     *
     * @param string $identifier Arbitrary identifier.
     * @param mixed $instance Arbitrary value.
     * @return void
     */
    public function bindInstance(string $identifier, mixed $instance): void;

    /**
     * Binds a factory to the given identifier.
     *
     * This allows the factory to later be invoked with the identifier.
     *
     * @param string $identifier Arbitrary identifier.
     * @param callable $factory Arbitrary value factory.
     * @return void
     */
    public function bindFactory(string $identifier, callable $factory): void;

    /**
     * Resolves an arbitrary value from the container matching the given identifier.
     *
     * @param string $identifier Arbitrary identifier.
     * @param mixed ...$parameters Arbitrary set of parameters to pass to the construction of the value.
     * @return mixed Arbitrary value matching the given identifier.
     */
    public function resolve(string $identifier, ...$parameters): mixed;

    /**
     * Invokes the given callable resolving any required dependencies.
     *
     * @param callable $callable Arbitrary callable.
     * @param mixed ...$parameters Arbitrary set of parameters to pass to the invocation.
     * @return mixed Callable invocation return value.
     */
    public function invoke(callable $callable, ...$parameters): mixed;

    /**
     * Adds an identifier alias.
     *
     * @param string $identifier Arbitrary identifier.
     * @param string $alias Arbitrary identifier alias.
     * @return void
     */
    public function alias(string $identifier, string $alias): void;
}
