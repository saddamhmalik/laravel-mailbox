<?php

declare(strict_types=1);

namespace LaravelMailbox\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use LaravelMailbox\Contracts\EmailRepositoryContract;
use LaravelMailbox\Contracts\EmailStorageContract;
use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Drivers\MailboxMailDriverRegistrar;
use LaravelMailbox\Http\Livewire\Inbox;
use LaravelMailbox\Http\Middleware\EnsureMailboxAccess;
use LaravelMailbox\Repositories\ArrayEmailRepository;
use LaravelMailbox\Repositories\DatabaseEmailRepository;
use LaravelMailbox\Services\EmailCaptureService;
use LaravelMailbox\Services\EmailQueryService;
use LaravelMailbox\Services\MailboxService;
use LaravelMailbox\Services\PruneService;
use LaravelMailbox\Storage\ArrayEmailStorage;
use LaravelMailbox\Storage\CacheEmailStorage;
use LaravelMailbox\Storage\DatabaseEmailStorage;
use LaravelMailbox\Support\EmailMessageParser;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class MailboxServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('mailbox')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_mailbox_emails_table')
            ->hasCommands(
                \LaravelMailbox\Commands\InstallCommand::class,
                \LaravelMailbox\Commands\ClearCommand::class,
                \LaravelMailbox\Commands\PruneCommand::class,
                \LaravelMailbox\Commands\OpenCommand::class,
            );
    }

    public function packageRegistered(): void
    {
        $this->registerStorageBindings();
        $this->registerCoreBindings();

        $this->app->singleton(EmailMessageParser::class);
        $this->app->singleton(PruneService::class);

        $this->app->afterResolving(MailManager::class, static function (MailManager $manager): void {
            MailboxMailDriverRegistrar::register($manager);
        });
    }

    public function packageBooted(): void
    {
        $this->registerRoutes();
        $this->registerMiddleware();
        $this->registerLivewireComponents();
        $this->registerAssetPublishing();
        $this->registerSchedule();
        $this->loadTestingHelpers();
        $this->configureModel();
    }

    private function registerAssetPublishing(): void
    {
        $dist = dirname(__DIR__, 2).'/dist';

        $this->publishes([
            $dist.'/mailbox.css' => public_path('vendor/mailbox/mailbox.css'),
            $dist.'/mailbox.js' => public_path('vendor/mailbox/mailbox.js'),
        ], 'mailbox-assets');
    }

    private function registerSchedule(): void
    {
        if (! config('mailbox.prune.schedule', true)) {
            return;
        }

        $this->app->afterResolving(\Illuminate\Console\Scheduling\Schedule::class, function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
            $schedule->command('mailbox:prune')
                ->daily()
                ->when(fn (): bool => (bool) config('mailbox.prune.enabled', true));
        });
    }

    private function registerRoutes(): void
    {
        if (! config('mailbox.enabled', true)) {
            return;
        }

        Route::group([
            'prefix' => config('mailbox.route.prefix', 'mailbox'),
            'middleware' => config('mailbox.route.middleware', ['web', 'mailbox']),
            'domain' => config('mailbox.route.domain'),
            'as' => 'mailbox.',
        ], function (): void {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        });
    }

    private function registerStorageBindings(): void
    {
        $this->app->singleton(EmailRepositoryContract::class, function (Application $app): EmailRepositoryContract {
            if (config('mailbox.storage.driver') === 'array') {
                return $app->make(ArrayEmailRepository::class);
            }

            return $app->make(DatabaseEmailRepository::class);
        });

        $this->app->singleton(EmailStorageContract::class, function (Application $app): EmailStorageContract {
            return match (config('mailbox.storage.driver', 'database')) {
                'array' => $app->make(ArrayEmailStorage::class),
                'cache' => $app->make(CacheEmailStorage::class),
                'sqlite', 'database' => $app->make(DatabaseEmailStorage::class),
                default => $app->make(DatabaseEmailStorage::class),
            };
        });

        $this->app->singleton(ArrayEmailStorage::class);
        $this->app->singleton(CacheEmailStorage::class);
        $this->app->singleton(DatabaseEmailStorage::class);
    }

    private function registerCoreBindings(): void
    {
        $this->app->singleton(EmailCaptureService::class, function (Application $app): EmailCaptureService {
            return new EmailCaptureService(
                storage: $app->make(EmailStorageContract::class),
                repository: $app->make(EmailRepositoryContract::class),
                maxEmails: (int) config('mailbox.storage.max_emails', 1000),
            );
        });

        $this->app->singleton(MailboxContract::class, function (Application $app): MailboxContract {
            $arrayStorage = config('mailbox.storage.driver') === 'array'
                ? $app->make(ArrayEmailStorage::class)
                : null;

            return new MailboxService(
                captureService: $app->make(EmailCaptureService::class),
                repository: $app->make(EmailRepositoryContract::class),
                arrayStorage: $arrayStorage,
            );
        });

        $this->app->singleton(EmailQueryService::class, function (Application $app): EmailQueryService {
            return new EmailQueryService(
                repository: $app->make(EmailRepositoryContract::class),
                arrayStorage: config('mailbox.storage.driver') === 'array'
                    ? $app->make(ArrayEmailStorage::class)
                    : null,
            );
        });
    }

    private function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('mailbox', EnsureMailboxAccess::class);
    }

    private function registerLivewireComponents(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        Livewire::component('mailbox.inbox', Inbox::class);
    }

    private function loadTestingHelpers(): void
    {
        require_once __DIR__.'/../Testing/functions.php';
    }

    private function configureModel(): void
    {
        if ($connection = config('mailbox.storage.connection')) {
            \LaravelMailbox\Models\MailboxEmail::resolveConnection($connection);
        }
    }

}
