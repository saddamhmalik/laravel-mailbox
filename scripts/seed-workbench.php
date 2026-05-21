<?php

declare(strict_types=1);

chdir(dirname(__DIR__));

$command = sprintf(
    '%s vendor/bin/testbench db:seed --class=%s --force',
    PHP_BINARY,
    escapeshellarg(Workbench\Database\Seeders\DatabaseSeeder::class),
);

passthru($command, $code);

exit($code);
