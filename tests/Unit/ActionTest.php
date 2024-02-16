<?php

use BradieTilley\Actions\Dispatcher\FakeDispatcher;
use BradieTilley\Actions\Events\ActionDispatched;
use BradieTilley\Actions\Events\ActionDispatchErrored;
use BradieTilley\Actions\Events\ActionDispatching;
use BradieTilley\Actions\Facade\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use SebastianBergmann\Timer\Duration;
use Tests\Fixtures\ExampleAction;
use Tests\Fixtures\ExampleActionWithError;
use Tests\Fixtures\ExampleActionWithFakeHandler;
use Tests\Fixtures\ExampleActionWithSlowProcess;

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

    /**
     * Try with no faking to ensure the handle method is run correctly
     */
    $result = ExampleActionWithFakeHandler::dispatch($expect);
    expect($result)->toBe([
        'foo' => 'bar',
    ]);

    /**
     * Try with faking but execution to ensure the handle method is run correctly
     */
    Action::fake()->allowExecution();

    $result = ExampleActionWithFakeHandler::dispatch($expect);
    expect($result)->toBe([
        'foo' => 'bar',
    ]);

    Action::assertDispatched(ExampleActionWithFakeHandler::class);
    Action::assertNotDispatched(ExampleAction::class);

    /**
     * Try with faking but no execution to ensure the handleFake method is run
     */
    Action::fake()->disallowExecution();
    Action::assertNotDispatched(ExampleActionWithFakeHandler::class);
    Action::assertNotDispatched(ExampleAction::class);

    $result = ExampleActionWithFakeHandler::dispatch($expect);
    expect($result)->toBe([
        'foo' => 'faked',
    ]);

    Action::assertDispatched(ExampleActionWithFakeHandler::class);
    Action::assertNotDispatched(ExampleAction::class);
});

test('events are fired correctly', function () {
    Event::fake();

    Action::dispatch($action = new ExampleAction([ 'foo' => 'bar' ]));

    Event::assertDispatched(fn (ActionDispatching $event) => $event->action === $action);
    Event::assertDispatched(fn (ActionDispatched $event) => $event->action === $action);

    /**
     * Try again with an action that errors out
     */

    Event::fake();

    $action = new ExampleActionWithError([]);
    expect(fn () => Action::dispatch($action))
        ->toThrow(InvalidArgumentException::class, 'This is a test exception');

    Event::assertDispatched(fn (ActionDispatching $event) => $event->action === $action);
    Event::assertDispatched(fn (ActionDispatchErrored $event) => $event->action === $action);
});

test('events are timed', function () {
    $times = Collection::make();
    Event::listen(fn (ActionDispatched $event) => $times->push($event->duration));

    ExampleAction::dispatch([ 'foo' => 'bar' ]);

    expect($times)->toHaveCount(1);
    /** @var Collection<int, Duration> $times */
    expect($times->first()->asMilliseconds())->toBeLessThan(1);

    ExampleActionWithSlowProcess::dispatch([]);

    expect($times)->toHaveCount(2);
    /** @var Collection<int, Duration> $times */
    expect($times->last()->asMilliseconds())->toBeGreaterThan(10)->toBeLessThan(15);
});
