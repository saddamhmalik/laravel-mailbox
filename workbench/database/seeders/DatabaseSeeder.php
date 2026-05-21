<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Mail::raw('Welcome to Laravel Mailbox!', function ($message): void {
            $message->to('user@example.com')
                ->subject('Welcome');
        });

        Mail::raw('Your invoice is ready.', function ($message): void {
            $message->to('billing@example.com')
                ->subject('Invoice #1001');
        });
    }
}
