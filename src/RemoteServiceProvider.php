<?php

namespace Spatie\Remote;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\Remote\Commands\RemoteCommand;

class RemoteServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-remote')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_remote_table')
            ->hasCommand(RemoteCommand::class);
    }
}
