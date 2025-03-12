<?php

declare(strict_types=1);

namespace Omega\Autoload;

use function array_merge;
use function array_unique;
use function is_nulL;
use function is_readable;
use function rtrim;
use function str_contains;
use function str_replace;
use function strlen;
use function strrpos;
use function strtolower;
use function substr;

/**
 * Class used to autoload classes following the WordPress coding standard.
 */
class Autoload
{
    /** @var array Holds a list of mappings between namespaces and their root directories. */
    private array $namespaceMappings = [];

    /**
     * Resolves a file from the class name and includes it if possible.
     *
     * @param string $className Holds the qualified class name.
     * @return bool Return true if the file was found and included.
     */
    public function loadClass(string $className): bool
    {
        $classFile = $this->getClassFile($className);

        if (!is_null($classFile)) {
            require_once $classFile;

            return true;
        }

        return false;
    }

    /**
     * Adds a mapping from a namespace to a directory.
     *
     * @param string $namespace Holds unqualified/qualified namespace name.
     * @param string $directory Holds the namespace root directory.
     * @return void
     */
    public function addNamespaceMapping(string $namespace, string $directory): void
    {
        $normalizedNamespace = rtrim($namespace, '\\');
        $normalizedDirectory = rtrim($directory, '/');

        $this->namespaceMappings[$normalizedNamespace] = array_unique(
            array_merge(
                $this->namespaceMappings[$normalizedNamespace] ?? [],
                [$normalizedDirectory]
            )
        );
    }

    /**
     * Resolves a file from the class name and returns it.
     *
     * Will use the registered namespace mappings to try to resolve the file and
     * will return the first match.
     *
     * @param string $className Holds the qualified class name.
     * @return string|null Return the class file if found.
     */
    private function getClassFile(string $className): ?string
    {
        foreach ($this->namespaceMappings as $namespace => $directories) {
            $classFile = $this->resolveClassFile($className, $namespace, $directories);

            if (!is_null($classFile)) {
                return $classFile;
            }
        }

        return null;
    }

    /**
     * Resolves a file from the class name and returns it.
     *
     * @param string $className  Holds the qualified class name.
     * @param string $namespace  Holds the unqualified/qualified namespace name.
     * @param array $directories Holds the namespace root directories.
     * @return string|null Return the class file if found.
     */
    private function resolveClassFile(string $className, string $namespace, array $directories): ?string
    {
        $classNamespace = $this->extractNamespace($className);

        if (strlen($namespace) > 0 && !str_contains($classNamespace, $namespace)) {
            return null;
        }

        foreach ($directories as $directory) {
            $path      = $this->pathFromNamespace(substr($classNamespace, strlen($namespace) + 1));
            $fileName  = $this->fileNameFromClassName($this->extractClass($className));
            $classFile = str_replace('//', '/', "{$directory}/{$path}/{$fileName}");

            if (is_readable($classFile)) {
                return $classFile;
            }
        }

        return null;
    }

    /**
     * Extracts the namespace portion of the class name.
     *
     * @param string $className Holds the qualified class name.
     * @return string Return unqualified/qualified namespace name.
     */
    private function extractNamespace(string $className): string
    {
        $lastDelimiter = strrpos($className, '\\');

        return false !== $lastDelimiter ? substr($className, 0, $lastDelimiter) : '';
    }

    /**
     * Extracts the class portion of the class name.
     *
     * @param string $className Holds the qualified class name.
     * @return string Return unqualified class name.
     */
    private function extractClass(string $className): string
    {
        $lastDelimiter = strrpos($className, '\\');

        return false !== $lastDelimiter ? substr($className, $lastDelimiter + 1) : $className;
    }

    /**
     * Converts a namespace to a path.
     *
     * @param string $namespace Unqualified/qualified namespace name.
     * @return string Return the file Name.
     */
    private function pathFromNamespace(string $namespace): string
    {
        return strtolower(str_replace(['\\', '_'], ['/', '-'], $namespace));
    }

    /**
     * Converts a class to a file.
     *
     * @param string $className Unqualified class name.
     * @return string Return the file name.
     */
    private function fileNameFromClassName(string $className): string
    {
        //$fileName = strtolower(str_replace('_', '-', $className));

        //return "class-{$fileName}.php";
        return $className . '.php';
    }
}