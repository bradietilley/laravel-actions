<?php

namespace BradieTilley\Actions;

use BradieTilley\Actions\Contracts\Actionable as ContractsAction;

abstract class Action implements ContractsAction
{
    use Dispatchable;
}
