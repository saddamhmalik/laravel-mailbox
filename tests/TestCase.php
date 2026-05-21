<?php

declare(strict_types=1);

namespace LaravelMailbox\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelMailbox\Providers\MailboxServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            MailboxServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.env', 'testing');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.array', [
            'driver' => 'array',
        ]);
        $app['config']->set('mailbox.enabled', true);
        $app['config']->set('mailbox.local_only', false);
        $app['config']->set('mailbox.storage.driver', 'database');
        $app['config']->set('mailbox.route.prefix', 'mailbox');
        $app['config']->set('mailbox.route.middleware', ['web', 'mailbox']);
        $app['config']->set('mail.default', 'mailbox');
        $app['config']->set('mail.mailers.mailbox', [
            'transport' => 'mailbox',
        ]);
        $app['config']->set('mail.mailers.array', [
            'transport' => 'array',
        ]);
    }

}
