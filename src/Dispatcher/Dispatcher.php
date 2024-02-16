<?php

namespace BradieTilley\Actions\Dispatcher;

use BradieTilley\Actions\Contracts\Action;
use BradieTilley\Actions\Contracts\Dispatcher as DispatcherContract;
use BradieTilley\Actions\Events\ActionDispatched;
use BradieTilley\Actions\Events\ActionDispatchErrored;
use BradieTilley\Actions\Events\ActionDispatching;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Event;
use SebastianBergmann\Timer\Timer;
use Throwable;

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
        Event::dispatch(new ActionDispatching($action));

        try {
            $timer = new Timer();
            $timer->start();

            /** @phpstan-ignore-next-line */
            $value = $this->container->call($action->handle(...));
        } catch (Throwable $error) {
            Event::dispatch(new ActionDispatchErrored($action, $error));

            throw $error;
        }

        $duration = $timer->stop();

        Event::dispatch(new ActionDispatched($action, $value, $duration));

        return $value;
    }
}
