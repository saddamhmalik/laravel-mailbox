<?php

declare(strict_types=1);

$pattern = __DIR__.'/../vendor/orchestra/testbench-core/laravel/database/migrations/*mailbox*';

foreach (glob($pattern) ?: [] as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
