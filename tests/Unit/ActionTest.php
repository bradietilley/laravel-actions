<?php

use BradieTilley\Actionables\Dispatcher\FakeDispatcher;
use BradieTilley\Actionables\Laravel\Action;
use Illuminate\Support\Facades\Bus;
use Tests\Fixtures\ExampleAction;
use Tests\Fixtures\ExampleActionWithFakeHandler;

// test('an action can be run', function () {
//     $expect = [
//         'foo' => 'bar',
//     ];

//     $actual = ExampleAction::run($expect);

//     expect($actual)->toBe($expect);
// });

// test('an action can be faked', function () {
//     Bus::fake();

//     $expect = [
//         'foo' => 'bar',
//     ];

//     $value = ExampleAction::run($expect);

//     Bus::assertDispatched(ExampleAction::class);
//     Bus::assertDispatched(fn (ExampleAction $action) => $action->value === $expect);
// });

test('an action can be run', function () {
    $expect = [
        'foo' => 'bar',
    ];

    $actual = ExampleAction::dispatch($expect);

    expect($actual)->toBe($expect);
});

test('an action can be faked', function () {
    $expect = [
        'foo' => 'bar',
    ];

    $fake = Action::fake();
    expect($fake)->toBeInstanceOf(FakeDispatcher::class);

    $result = ExampleAction::dispatch($expect);
    expect($result)->toBe(null);

    Action::assertDispatched(ExampleAction::class);
    Action::assertNotDispatched(ExampleActionWithFakeHandler::class);
});

test('an action can be faked with invocation enabled', function () {
    $expect = [
        'foo' => 'bar',
    ];

    $fake = Action::fake()->allowExecution();

    $result = ExampleActionWithFakeHandler::dispatch($expect);
    expect($result)->toBe([
        'foo' => 'bar',
    ]);

    Action::assertDispatched(ExampleActionWithFakeHandler::class);
    Action::assertNotDispatched(ExampleAction::class);
});
