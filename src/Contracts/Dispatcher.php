<?php

namespace BradieTilley\Actions\Contracts;

interface Dispatcher
{
    public function dispatch(Action $action): mixed;
}
