<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(php83: true)
    ->withPhpVersion(PhpVersion::PHP_83)
    ->withImportNames()
    ->withFluentCallNewLine()
    ;
