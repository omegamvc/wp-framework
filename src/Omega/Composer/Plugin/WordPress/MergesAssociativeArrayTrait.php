<?php

declare(strict_types=1);

namespace Omega\Composer\Plugin\WordPress;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function is_array;

trait MergesAssociativeArrayTrait
{
    protected function mergeAssociativeArrays(array $a, array $b, string $strategy): array
    {
        foreach ($b as $key => $value) {
            if (!array_key_exists($key, $a)) {
                $a[$key] = $value;

                continue;
            }

            if ($this->isAssociativeArray($a[$key]) && $this->isAssociativeArray($value)) {
                $a[$key] = $this->mergeAssociativeArrays($a[$key], $value, $strategy);

                continue;
            }

            if ($this->shouldMergeIndexedArrays($strategy) && is_array($a[$key]) && is_array($value)) {
                $a[$key] = array_values(array_unique(array_merge($a[$key], $value)));

                continue;
            }

            $a[$key] = $value;
        }

        return $a;
    }

    private function isAssociativeArray($value): bool
    {
        return is_array($value) && count(array_filter(array_keys($value), 'is_string')) > 0;
    }

    private function shouldMergeIndexedArrays(string $strategy): bool
    {
        return $strategy === MergeStrategy::MERGE_INDEXED_ARRAYS;
    }
}