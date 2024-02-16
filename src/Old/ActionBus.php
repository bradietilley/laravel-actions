<?php

declare(strict_types=1);

namespace BradieTilley\Actionables\Old;

use Illuminate\Bus\Dispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

abstract class ActionBus implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public static function getDispatcher(): BusDispatcher
    {
        return app(Dispatcher::class);
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
     *
     * Queueable jobs will be dispatched to the "sync" queue.
     */
    public static function run(...$arguments)
    {
        $dispatcher = self::getDispatcher();

        return $dispatcher->dispatchSync(new static(...$arguments));
    }
}
