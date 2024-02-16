<?php

namespace BradieTilley\Actions\Events;

use BradieTilley\Actions\Contracts\Actionable;
use Throwable;

class ActionDispatchErrored
{
    public function __construct(public readonly Actionable $action, public readonly Throwable $error)
    {
    }
}
