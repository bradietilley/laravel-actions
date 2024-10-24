<?php

namespace BradieTilley\Actions\Middleware;

use BradieTilley\Actions\Contracts\Actionable;
use Closure;
use Throwable;

class RunWithoutExceptions
{
    protected bool $report = true;

    protected Closure|null $rescue = null;

    public static function make(): static
    {
        return new static();
    }

    public function report(bool $report = true): static
    {
        $this->report = $report;

        return $this;
    }

    public function rescue(Closure|null $rescue): static
    {
        $this->rescue = $rescue;

        return $this;
    }

    public function handle(Actionable $action, Closure $next): mixed
    {
        try {
            return $next($action);
        } catch (Throwable $e) {
            if ($this->report) {
                report($e);
            }

            return value($this->rescue, $e);
        }
    }
}
