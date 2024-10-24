<?php

namespace BradieTilley\Actions\Middleware;

use BradieTilley\Actions\Contracts\Actionable;
use Closure;
use Illuminate\Support\Facades\DB;

class RunWithinTransaction
{
    public function __construct(public int $attempts = 1)
    {
    }

    public static function attempts(int $attempts): static
    {
        return new static($attempts);
    }

    public function handle(Actionable $action, Closure $next): mixed
    {
        return DB::transaction(
            fn () => $next($action),
            attempts: $this->attempts,
        );
    }
}
