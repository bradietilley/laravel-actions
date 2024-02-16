<?php

namespace BradieTilley\Actions\Contracts;

interface Dispatcher
{
    public function dispatch(Actionable $action): mixed;
}
