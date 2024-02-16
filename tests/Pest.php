<?php

use BradieTilley\Actionables\Dispatcher\FakeDispatcher;

uses(Tests\TestCase::class)->in('Feature', 'Unit');

function getDispatched(FakeDispatcher $dispatcher): array
{
    $reflection = new ReflectionProperty($dispatcher, 'actions');

    return $reflection->getValue($dispatcher);
}

function invokeProtectedMethod(object $object, string $method, mixed ...$args): mixed
{
    $reflection = new ReflectionMethod($object, $method);

    return $reflection->invoke($object, ...$args);
}
