<?php

namespace Workbench\App\Actions;

use BradieTilley\Actions\Action;
use InvalidArgumentException;

class ExampleActionWithError extends Action
{
    public function __construct(public array $value)
    {
    }

    public function handle(): array
    {
        throw new InvalidArgumentException('This is a test exception');
    }
}
