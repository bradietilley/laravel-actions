<?php

namespace BradieTilley\Actions\Events;

use BradieTilley\Actions\Contracts\Action;

class ActionDispatched
{
    public function __construct(public readonly Action $action, public readonly mixed $value)
    {
    }
}
