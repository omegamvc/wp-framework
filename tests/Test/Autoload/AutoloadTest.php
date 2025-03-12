<?php

namespace Test\Autoload;

use Omega\Autoload\Autoload;
use PHPUnit\Framework\TestCase;

use function class_exists;
use function spl_autoload_register;
use function spl_autoload_unregister;

/**
 */
class AutoloadTest extends TestCase
{
    private Autoload $autoloader;

    /**
     *
     */
    public function registerAutoloader(): void
    {
        $this->autoloader = new Autoload();

        spl_autoload_register([$this->autoloader, 'loadClass']);
    }

    /**
     * @after
     */
    public function unregisterAutoloader(): void
    {
        spl_autoload_unregister([$this->autoloader, 'loadClass']);
    }

    /**
     * @test
     */
    public function testClassShouldNotBeLoadedByDefault(): void
    {
        $this->assertFalse(class_exists('Test\\Autoloader\\Fixtures\\TestClass'));
    }

    /**
     * @test
     */
    public function testShouldFailIfClassNotFound(): void
    {
        $this->autoloader->addNamespaceMapping('Wordpress', __DIR__);
        $this->autoloader->addNamespaceMapping('Other_Namespace', __DIR__);

        $this->assertFalse(class_exists('Test\\Autoloader\\Fixtures\\TestClass'));
    }

    /**
     * @test
     */
    public function testShouldLoadClass(): void
    {
        $this->autoloader->addNamespaceMapping('Wordpress', __DIR__ . '/Fixtures/');

        $this->assertTrue(class_exists('Test\\Autoloader\\Fixtures\\TestClass'));
    }

    /**
     * @test
     */
    public function testShouldLoadNestedClass(): void
    {
        $this->autoloader->addNamespaceMapping('Wordpress', __DIR__ . '/Fixtures/');

        $this->assertTrue(class_exists('Test\\Autoloader\\Fixtures\\Nested\\TestClass'));
    }

    /**
     * @test
     */
    public function testShouldAcceptNamespaceWithTrailingDelimiter(): void
    {
        $this->autoloader->addNamespaceMapping('Wordpress\\', __DIR__ . '/Fixtures/');

        $this->assertTrue(class_exists('Test\\Autoloader\\Fixtures\\TestClass'));
    }

    /**
     * @test
     */
    public function testShouldAcceptNestedNamespace(): void
    {
        $this->autoloader->addNamespaceMapping('Wordpress\\Nested\\', __DIR__ . '/Fixtures/Nested/');

        $this->assertTrue(class_exists('Test\\Autoloader\\Fixtures\\Nested\\TestClass'));
    }

    /**
     * @test
     */
    public function testShouldAcceptEmptyNamespace(): void
    {
        $this->autoloader->addNamespaceMapping('', __DIR__ . '/Fixtures/Empty/');

        $this->assertTrue(class_exists('Test\Autoloader\Fixtures\Empty\TestClass'));
    }

    /**
     * @test
     */
    public function testShouldMergeNamespaceMappings(): void
    {
        $this->autoloader->addNamespaceMapping('Wordpress\\Nested\\', __DIR__);
        $this->autoloader->addNamespaceMapping('Wordpress\\Nested\\', __DIR__ . '/Fixtures/Nested/');

        $this->assertTrue(class_exists('Test\\Autoloader\\Fixtures\\Nested\\TestClass'));
    }
}
