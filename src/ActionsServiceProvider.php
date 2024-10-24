<?php

namespace BradieTilley\Actions;

use BradieTilley\Actions\Contracts\Dispatcher;
use BradieTilley\Actions\Dispatcher\Dispatcher as DispatcherClass;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ActionsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('actions')
            ->hasViews('laravel-actions');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Dispatcher::class, DispatcherClass::class);
    }
}
