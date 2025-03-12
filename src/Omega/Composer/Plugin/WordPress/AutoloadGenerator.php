<?php

declare(strict_types=1);

namespace Omega\Composer\Plugin\WordPress;

use Composer\Autoload\AutoloadGenerator as ComposerAutoloadGenerator;
use Composer\Package\PackageInterface;

use function compact;

class AutoloadGenerator extends ComposerAutoloadGenerator
{
    public function parseAutoloads(
        array $packageMap,
        PackageInterface $rootPackage,
        $filterOutRequireDevPackages = false
    ): array {
        if ($filterOutRequireDevPackages) {
            $packageMap = $this->filterPackageMap($packageMap, $rootPackage);
        }

        $wordpress = $this->parseAutoloadsType($packageMap, 'wordpress', $rootPackage);

        return compact('wordpress');
    }
}