<?php

namespace BradieTilley\Actions;

use BradieTilley\Actions\Concerns\Dispatchable;
use BradieTilley\Actions\Contracts\Actionable;

abstract class Action implements Actionable
{
    use Dispatchable;

    /**
     * Middleware to pipe the action through before starting.
     *
     * @return array<int, class-string|object>
     */
    public function middleware(): array
    {
        return [];
    }
}
