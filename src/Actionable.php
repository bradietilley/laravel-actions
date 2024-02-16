<?php

namespace BradieTilley\Actionables;

use BradieTilley\Actionables\Contracts\Dispatcher;

trait Actionable
{
    public static function dispatch(mixed ...$arguments): mixed
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = app(Dispatcher::class);

        /** @phpstan-ignore-next-line */
        $action = new static(...$arguments);

        return $dispatcher->dispatch($action);
    }
}
