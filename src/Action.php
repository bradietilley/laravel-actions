<?php

namespace BradieTilley\Actionables;

use BradieTilley\Actionables\Contracts\Action as ContractsAction;

abstract class Action implements ContractsAction
{
    use Actionable;
}
