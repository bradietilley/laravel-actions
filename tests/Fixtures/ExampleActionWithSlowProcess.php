<?php

namespace Tests\Fixtures;

use BradieTilley\Actions\Action;

class ExampleActionWithSlowProcess extends Action
{
    public function __construct(public array $value)
    {
    }

    public function handle(): void
    {
        usleep(10_000); // 10ms
    }
}
