<?php

namespace BradieTilley\Actionables;

use BradieTilley\Actionables\Contracts\Dispatcher;

trait Actionable
{
    public static function dispatch(...$arguments): mixed
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = app(Dispatcher::class);

        return $dispatcher->dispatch(new static(...$arguments));
    }
}
