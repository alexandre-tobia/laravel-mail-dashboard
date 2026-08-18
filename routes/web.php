<?php

use Copain\LaravelMailDashboard\Http\Controllers\AssetsController;
use Copain\LaravelMailDashboard\Http\Controllers\DashboardController;
use Copain\LaravelMailDashboard\Http\Controllers\EmailsController;
use Copain\LaravelMailDashboard\Http\Middleware\Authorize;
use Copain\LaravelMailDashboard\Http\Middleware\EnsureDashboardEnabled;
use Illuminate\Support\Facades\Route;

Route::middleware(array_merge((array) config('mail-dashboard.middleware', ['web']), [EnsureDashboardEnabled::class, Authorize::class]))
    ->prefix((string) config('mail-dashboard.path', 'mail-dashboard'))
    ->name('mail-dashboard.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('index');
        Route::get('/assets/{asset}', AssetsController::class)->name('asset');

        Route::get('/api/emails', [EmailsController::class, 'index'])->name('api.emails.index');
        Route::delete('/api/emails', [EmailsController::class, 'destroyAll'])->name('api.emails.destroy-all');
        Route::get('/api/emails/{id}', [EmailsController::class, 'show'])
            ->where('id', '[0-9]+_[a-f0-9]+')
            ->name('api.emails.show');
        Route::delete('/api/emails/{id}', [EmailsController::class, 'destroy'])
            ->where('id', '[0-9]+_[a-f0-9]+')
            ->name('api.emails.destroy');
        Route::get('/api/emails/{id}/attachments/{index}', [EmailsController::class, 'attachment'])
            ->where('id', '[0-9]+_[a-f0-9]+')
            ->whereNumber('index')
            ->name('api.emails.attachment');
    });
