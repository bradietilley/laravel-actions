<?php

namespace BradieTilley\Actions\Dispatcher;

use BradieTilley\Actions\Contracts\Actionable;
use BradieTilley\Actions\Contracts\Dispatcher as DispatcherContract;
use BradieTilley\Actions\Duration;
use BradieTilley\Actions\Events\ActionDispatched;
use BradieTilley\Actions\Events\ActionDispatching;
use BradieTilley\Actions\Events\ActionFailed;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Pipeline;
use Throwable;

class Dispatcher implements DispatcherContract
{
    use ActionRecording;

    public function __construct(
        public readonly Container $container,
        public readonly EventsDispatcher $events,
        public readonly DatabaseManager $db,
    ) {
    }

    /**
     * Run the action
     */
    public function dispatch(Actionable $action): mixed
    {
        $this->recordAction($action);

        if (empty($middleware = $action->middleware())) {
            return $this->doDispatch($action);
        }

        return Pipeline::send($action)
            ->via('handle')
            ->through($middleware)
            ->then($this->doDispatch(...));
    }

    /**
     * Run the action
     */
    public function doDispatch(Actionable $action): mixed
    {
        $this->events->dispatch(new ActionDispatching($action));
        $start = Duration::start();

        try {
            /** @phpstan-ignore-next-line */
            $value = $this->container->call($action->handle(...));
        } catch (Throwable $error) {
            $this->events->dispatch(new ActionFailed($action, $error));

            throw $error;
        }

        $duration = Duration::stop($start);
        $this->events->dispatch(new ActionDispatched($action, $value, $duration));

        return $value;
    }
}
