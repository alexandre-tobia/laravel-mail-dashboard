<?php

namespace Copain\LaravelMailDashboard;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Copain\LaravelMailDashboard\Commands\LaravelMailDashboardCommand;

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
