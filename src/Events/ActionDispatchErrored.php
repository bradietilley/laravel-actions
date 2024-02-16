<?php

namespace BradieTilley\Actions\Events;

use BradieTilley\Actions\Contracts\Action;
use Throwable;

class ActionDispatchErrored
{
    public function __construct(public readonly Action $action, public readonly Throwable $error)
    {
    }
}
