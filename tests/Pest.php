<?php

use BradieTilley\Actionables\Dispatcher\FakeDispatcher;

uses(Tests\TestCase::class)->in('Feature', 'Unit');

function getDispatched(FakeDispatcher $dispatcher): array
{
    $reflection = new ReflectionProperty($dispatcher, 'actions');

    return $reflection->getValue($dispatcher);
}
