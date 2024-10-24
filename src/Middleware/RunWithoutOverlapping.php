<?php

namespace BradieTilley\Actions\Middleware;

use BradieTilley\Actions\Contracts\Actionable;
use Closure;
use Illuminate\Support\Facades\Cache;

class RunWithoutOverlapping
{
    public function __construct(
        public string $key,
        public int $seconds = 0,
        public string|null $owner = null,
    ) {
    }

    public static function make(string $key, int $seconds = 0, string|null $owner = null): static
    {
        return new static($key, $seconds, $owner);
    }

    public function handle(Actionable $action, Closure $next): mixed
    {
        $lock = Cache::lock($this->key, $this->seconds, $this->owner);

        return $lock->get(fn () => $next($action));
    }
}
