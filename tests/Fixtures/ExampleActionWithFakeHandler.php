<?php

namespace Tests\Fixtures;

use BradieTilley\Actionables\Action;
use BradieTilley\Actionables\Contracts\ActionFake;

class ExampleActionWithFakeHandler extends Action implements ActionFake
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
