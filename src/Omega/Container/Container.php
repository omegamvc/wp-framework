<?php


namespace Omega\Container;

use Closure;
use Omega\Container\Exceptions\ClassNotFoundException;
use Omega\Container\Exceptions\DependencyResolutionException;
use Omega\Container\Exceptions\NotInstantiableException;
use Omega\Container\Exceptions\RecursiveDependencyException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;

use function array_key_exists;
use function array_map;
use function array_pop;
use function count;
use function in_array;
use function is_null;

/**
 * A dependency injection container implementation.
 */
class Container implements ContainerInterface
{
    /** @var array<string, callable> Registered bindings. */
    private array $bindings = [];

    /** @var array<string, mixed> Registered instances. */
    private array $instances = [];

    /** @var array<string, string> Registered identifier alias. */
    private array $aliases = [];

    /** @var string[] Stack keeping track of dependencies to prevent circular dependencies. */
    private array $dependencyStack = [];

    /**
     * {@inheritdoc}
     */
    public function bindClass(string $identifier, string $className): void
    {
        // @phan-suppress-next-line PhanParamNameIndicatingUnusedInClosure
        $this->bindings[$identifier] = fn($_, ...$parameters) => $this->resolve($className, ...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function bindInstance(string $identifier, $instance): void
    {
        $this->instances[$identifier] = $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function bindFactory(string $identifier, callable $factory): void
    {
        $this->bindings[$identifier] = $factory;
    }

    /**
     * {@inheritdoc}
     * @throws ReflectionException
     * @throws RecursiveDependencyException If any dependencies are recursive.
     */
    public function resolve(string $identifier, ...$parameters): mixed
    {
        $resolvedIdentifier = $this->resolveIdentifier($identifier);

        if (in_array($resolvedIdentifier, $this->dependencyStack, true)) {
            throw new RecursiveDependencyException($resolvedIdentifier);
        }

        $this->dependencyStack[] = $resolvedIdentifier;

        if (array_key_exists($resolvedIdentifier, $this->instances)) {
            array_pop($this->dependencyStack);

            return $this->instances[$resolvedIdentifier];
        }

        $instance = array_key_exists($resolvedIdentifier, $this->bindings) ?
            // @phan-suppress-next-line PhanParamTooMany
            $this->bindings[$resolvedIdentifier]($this, ...$parameters) :
            $this->createInstance($resolvedIdentifier, ...$parameters);

        array_pop($this->dependencyStack);

        return $instance;
    }

    /**
     * {@inheritdoc}
     * @throws ReflectionException
     */
    public function invoke(callable $callable, ...$parameters): mixed
    {
        $reflection = new ReflectionFunction(Closure::fromCallable($callable));

        if ($reflection->getNumberOfParameters() === 0) {
            return $reflection->invoke();
        }

        return $reflection->invokeArgs($this->resolveMethodDependencies($reflection, $parameters));
    }

    /**
     * {@inheritdoc}
     */
    public function alias(string $identifier, string $alias): void
    {
        while (array_key_exists($identifier, $this->aliases)) {
            $identifier = $this->aliases[$identifier];
        }

        $this->aliases[$alias] = $identifier;
    }

    /**
     * Resolves the origin identifier behind a possible set of aliases.
     *
     * The set of aliases is flattened when adding an alias so all keys of the
     * alias array points to origin identifiers.
     *
     * @param string $identifier Arbitrary identifier.
     * @return string Origin identifier.
     */
    private function resolveIdentifier(string $identifier): string
    {
        return $this->aliases[$identifier] ?? $identifier;
    }

    /**
     * Creates an instance of the given class name.
     *
     * @param string $className Arbitrary class name.
     * @param mixed ...$parameters Arbitrary set of parameters to pass to the constructor.
     * @return mixed Class instance.
     * @throws ClassNotFoundException If class cannot be loaded.
     * @throws NotInstantiableException|ReflectionException If class is not instantiable.
     */
    private function createInstance(string $className, ...$parameters): mixed
    {
        try {
            $reflection = new ReflectionClass($className);
        } catch (ReflectionException $_) {
            throw new ClassNotFoundException($className);
        }

        if (!$reflection->isInstantiable()) {
            throw new NotInstantiableException($className);
        }

        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return $reflection->newInstance();
        }

        return $reflection->newInstanceArgs($this->resolveMethodDependencies($constructor, $parameters));
    }

    /**
     * Resolves any unmet dependencies of the given method.
     *
     * @param ReflectionFunctionAbstract $method Arbitrary method instance.
     * @param array $parameters Arbitrary parameters.
     * @return array Set of resolved method dependencies.
     * @throws ReflectionException
     */
    private function resolveMethodDependencies(ReflectionFunctionAbstract $method, array $parameters): array
    {
        if ($method->getNumberOfParameters() === count($parameters)) {
            return $parameters;
        }

        return array_map(
            fn($parameter) => array_key_exists($parameter->getPosition(), $parameters) ?
                $parameters[$parameter->getPosition()] :
                $this->resolveMethodParameter($parameter),
            $method->getParameters()
        );
    }

    /**
     * Resolves a value for the given parameter.
     *
     * @param ReflectionParameter $parameter Arbitrary method parameter.
     * @return mixed Arbitrary resolved value.
     * @throws DependencyResolutionException|ReflectionException When a method dependency cannot be resolved.
     */
    private function resolveMethodParameter(ReflectionParameter $parameter): mixed
    {
        if ($parameter->isOptional()) {
            return $parameter->getDefaultValue();
        }

        $type = $parameter->getType();

        if (is_null($type) || ($type instanceof ReflectionNamedType && $type->isBuiltin())) {
            throw new DependencyResolutionException($parameter);
        }

        return $this->resolve($type instanceof ReflectionNamedType ? $type->getName() : (string)$type);
    }
}
