<?php

namespace BradieTilley\Actions\Events;

use BradieTilley\Actions\Contracts\Actionable;

class ActionDispatching
{
    public function __construct(public readonly Actionable $action)
    {
    }
}
