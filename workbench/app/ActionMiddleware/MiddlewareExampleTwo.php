<?php

namespace Workbench\App\ActionMiddleware;

use BradieTilley\Actions\Action;
use Closure;

class MiddlewareExampleTwo extends MiddlewareExample
{
    public function handle(Action $action, Closure $next): mixed
    {
        MiddlewareExample::$history[] = [$this::class, $action];

        return $next($action);
    }
}
