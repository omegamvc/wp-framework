<?php

namespace Test\Container;

use Omega\Container\Container;
use Omega\Container\ContainerInterface;
use Omega\Container\Exceptions\ClassNotFoundException;
use Omega\Container\Exceptions\DependencyResolutionException;
use Omega\Container\Exceptions\NotInstantiableException;
use Omega\Container\Exceptions\RecursiveDependencyException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Test\Container\Fixtures\A;
use Test\Container\Fixtures\ARecursive;
use Test\Container\Fixtures\B;
use Test\Container\Fixtures\C;
use Test\Container\Fixtures\D;
use Test\Container\Fixtures\E;
use Test\Container\Fixtures\F;
use Test\Container\Fixtures\AInterface;

use function class_implements;
use function get_class;

/**
 */
class ContainerTest extends TestCase
{
    private Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
    }

    /**
     * @Container
     */
    public function testShouldImplementContainerInterface(): void
    {
        $this->assertContains(ContainerInterface::class, class_implements(get_class($this->container)));
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveBoundInstance(): void
    {
        $value = 'value';
        $this->container->bindInstance('identifier', $value);

        $this->assertSame($value, $this->container->resolve('identifier'));
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveBoundFactoryValue(): void
    {
        $value = 'value';
        $this->container->bindFactory('identifier', fn() => $value);

        $this->assertSame($value, $this->container->resolve('identifier'));
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveBoundClass(): void
    {
        $this->container->bindClass(AInterface::class, A::class);

        $this->assertInstanceOf(A::class, $this->container->resolve(AInterface::class));
    }

    /**
     * @throws ClassNotFoundException
     * @throws ReflectionException
     */
    public function testShouldThrowExceptionIfBoundClassCannotBeFound(): void
    {
        $this->expectException(ClassNotFoundException::class);

        $this->container->bindClass(AInterface::class, 'Non_Existing_Class');

        $this->container->resolve(AInterface::class);
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveClassInstance(): void
    {
        $this->assertInstanceOf(A::class, $this->container->resolve(A::class));
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveAlias(): void
    {
        $value = 'value';
        $this->container->bindInstance('identifier', $value);
        $this->container->alias('identifier', 'alias');

        $this->assertSame($value, $this->container->resolve('alias'));
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveNestedAlias(): void
    {
        $value = 'value';
        $this->container->bindInstance('identifier', $value);
        $this->container->alias('identifier', 'alias');
        $this->container->alias('alias', 'another-alias');

        $this->assertSame($value, $this->container->resolve('another-alias'));
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldAcceptConstructorDependencies(): void
    {
        $b = $this->container->resolve(B::class, new A());

        $this->assertInstanceOf(B::class, $b);
        $this->assertInstanceOf(A::class, $b->a);
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveConstructorDependencies(): void
    {
        $this->assertInstanceOf(A::class, $this->container->resolve(B::class)->a);
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveRecursiveDependencies(): void
    {
        $this->assertInstanceOf(A::class, $this->container->resolve(C::class)->b->a);
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldUseProvidedDependencies(): void
    {
        $message = 'message';
        $d = $this->container->resolve(D::class, $message);

        $this->assertSame($message, $d->message);
        $this->assertInstanceOf(A::class, $d->a);
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldUseDefaultValueIfAvailable(): void
    {
        $message = 'message';
        $e = $this->container->resolve(E::class, $message);

        $this->assertSame($message, $e->message);
        $this->assertNull($e->a);
    }

    /**
     * @throws DependencyResolutionException
     * @throws ReflectionException
     */
    public function testShouldThrowExceptionIfADependencyCannotBeResolved(): void
    {
        $this->expectException(DependencyResolutionException::class);

        $this->container->resolve(F::class);
    }

    /**
     * @throws RecursiveDependencyException
     * @throws ReflectionException
     */
    public function testShouldThrowExceptionIfDependencyIsRecursive(): void
    {
        $this->expectException(RecursiveDependencyException::class);

        $this->container->resolve(ARecursive::class);
    }

    /**
     * @throws ReflectionException
     * @throws NotInstantiableException
     */
    public function testShouldThrowExceptionIfNonInstantiable(): void
    {
        $this->expectException(NotInstantiableException::class);

        $this->container->resolve(AInterface::class);
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveCallable(): void
    {
        $value = 'value';

        $this->assertSame($value, $this->container->invoke(fn() => $value));
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldResolveInvocationDependencies(): void
    {
        $this->assertInstanceof(A::class, $this->container->invoke(fn(B $b) => $b->a));
    }

    /**
     * @throws ReflectionException
     */
    public function testShouldThrowExceptionIfInvocationDependencyCannotBeResolved(): void
    {
        $this->expectException(DependencyResolutionException::class);

        $this->container->invoke(fn(string $message) => $message);
    }
}