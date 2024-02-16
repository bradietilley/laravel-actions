<?php

namespace BradieTilley\Actions\Dispatcher;

use BradieTilley\Actions\Contracts\Action;
use BradieTilley\Actions\Contracts\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Container\Container;

class Dispatcher implements DispatcherContract
{
    public function __construct(public readonly Container $container)
    {
    }

    /**
     * Run the action
     */
    public function dispatch(Action $action): mixed
    {
        /** @phpstan-ignore-next-line */
        return $this->container->call($action->handle(...));
    }
}
