<?php

namespace Workbench\App\ActionMiddleware;

use BradieTilley\Actions\Action;
use Closure;
use Workbench\App\Actions\ExampleActionWithMiddleware;

class MiddlewareExampleOne extends MiddlewareExample
{
    public function handle(Action $action, Closure $next): mixed
    {
        MiddlewareExample::$history[] = [$this::class, $action];

        if ($action instanceof ExampleActionWithMiddleware) {
            if (($action->value['foo'] ?? '') === 'stop') {
                return 5678;
            }
        }

        return $next($action);
    }
}
