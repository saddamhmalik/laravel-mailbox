<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelMailbox\Http\Controllers\AssetController;
use LaravelMailbox\Http\Controllers\EmailController;
use LaravelMailbox\Http\Livewire\Inbox;

Route::get('/', Inbox::class)->name('index');
Route::get('/assets/mailbox.css', [AssetController::class, 'css'])->name('assets.css');
Route::get('/assets/mailbox.js', [AssetController::class, 'js'])->name('assets.js');
Route::delete('/emails/{id}', [EmailController::class, 'destroy'])->name('emails.destroy');
