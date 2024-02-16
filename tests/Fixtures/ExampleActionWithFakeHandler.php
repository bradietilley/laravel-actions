<?php

namespace Tests\Fixtures;

use BradieTilley\Actionables\Action;

class ExampleActionWithFakeHandler extends Action
{
    public function __construct(public array $value)
    {
    }

    public function handle(): array
    {
        return $this->value;
    }

    public function handleFake(): array
    {
        return [
            'foo' => 'foo',
        ];
    }
}
