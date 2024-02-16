<?php

namespace Tests\Fixtures;

use BradieTilley\Actionables\Action;

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
