<?php

namespace BradieTilley\Actions\Middleware;

use BradieTilley\Actions\Contracts\Actionable;
use Closure;
use Illuminate\Database\Eloquent\Model;

class RefreshReturnedModel
{
    public function handle(Actionable $action, Closure $next): mixed
    {
        $value = $next($action);

        if ($value instanceof Model) {
            $value->refresh();
        }

        return $value;
    }
}
