<?php

namespace BradieTilley\Actionables\Laravel;

use BradieTilley\Actionables\Contracts\Dispatcher as DispatcherContract;
use BradieTilley\Actionables\Dispatcher\Dispatcher;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ActionServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(Dispatcher::class, function ($app) {
            return new Dispatcher($app);
        });

        $this->app->alias(Dispatcher::class, DispatcherContract::class);
    }

    public function boot(): void
    {
    }

    public function provides(): array
    {
        return [
            Dispatcher::class,
            DispatcherContract::class,
        ];
    }
}
