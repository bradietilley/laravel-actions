<?php

namespace BradieTilley\Actions\Concerns;

use BradieTilley\Actions\Contracts\Dispatcher;

trait Dispatchable
{
    public static function dispatch(mixed ...$arguments): mixed
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = app(Dispatcher::class);

        /** @var class-string<static> */
        $class = app()->getAlias(static::class);

        /** @phpstan-ignore-next-line */
        $action = new $class(...$arguments);

        return $dispatcher->dispatch($action);
    }
}
