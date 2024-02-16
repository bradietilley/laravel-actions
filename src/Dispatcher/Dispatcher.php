<?php

namespace BradieTilley\Actionables\Dispatcher;

use BradieTilley\Actionables\Action;
use BradieTilley\Actionables\Contracts\Dispatcher as DispatcherContract;
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
        return $this->container->call($action->handle(...));
    }
}
