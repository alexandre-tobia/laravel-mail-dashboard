<?php

namespace Copain\LaravelMailDashboard;

use Copain\LaravelMailDashboard\Commands\LaravelMailDashboardCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMailDashboardServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-mail-dashboard')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_mail_dashboard_table')
            ->hasCommand(LaravelMailDashboardCommand::class);
    }
}
