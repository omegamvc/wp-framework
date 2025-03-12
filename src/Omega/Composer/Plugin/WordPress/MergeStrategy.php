<?php

declare(strict_types=1);

namespace Omega\Composer\Plugin\WordPress;

class MergeStrategy
{
    public const string REPLACE_INDEXED_ARRAYS = 'REPLACE_INDEXED_ARRAYS';
    public const string MERGE_INDEXED_ARRAYS = 'MERGE_INDEXED_ARRAYS';
}