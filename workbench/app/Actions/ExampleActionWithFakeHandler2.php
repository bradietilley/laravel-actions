<?php

namespace Workbench\App\Actions;

use BradieTilley\Actions\Action;
use BradieTilley\Actions\Contracts\IsFakeable;

class ExampleActionWithFakeHandler2 extends Action implements IsFakeable
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
            'foo' => 'faked',
        ];
    }
}
