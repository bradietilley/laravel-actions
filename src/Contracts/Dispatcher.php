<?php

namespace BradieTilley\Actionables\Contracts;

interface Dispatcher
{
    public function dispatch(Action $action): mixed;
}
