<?php

namespace BradieTilley\Actions;

use BradieTilley\Actions\Contracts\Action as ContractsAction;

abstract class Action implements ContractsAction
{
    use Actionable;
}
