<?php

namespace BradieTilley\Actionables\Contracts;

use BradieTilley\Actionables\Action;

interface Dispatcher
{
    public function dispatch(Action $action): mixed;
}
