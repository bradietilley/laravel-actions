<?php

namespace BradieTilley\Actions\Events;

use BradieTilley\Actions\Contracts\Action;

class ActionDispatching
{
    public function __construct(public readonly Action $action)
    {
    }
}
