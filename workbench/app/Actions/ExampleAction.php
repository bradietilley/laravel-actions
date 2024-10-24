<?php

namespace Workbench\App\Actions;

use BradieTilley\Actions\Action;

class ExampleAction extends Action
{
    public function __construct(public array $value)
    {
    }

    public function handle(): array
    {
        return $this->value;
    }
}
