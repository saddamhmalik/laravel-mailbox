<?php

declare(strict_types=1);

$dirs = [
    'workbench/bootstrap/cache',
    'workbench/storage/framework/cache',
    'workbench/storage/framework/sessions',
    'workbench/storage/framework/views',
    'workbench/storage/logs',
    'workbench/public',
];

foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
