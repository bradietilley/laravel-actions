<?php

use BradieTilley\Actions\Dispatcher\FakeDispatcher;
use BradieTilley\Actions\Events\ActionDispatched;
use BradieTilley\Actions\Events\ActionDispatching;
use BradieTilley\Actions\Events\ActionFailed;
use BradieTilley\Actions\Facades\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use SebastianBergmann\Timer\Duration;
use Workbench\App\ActionMiddleware\MiddlewareExample;
use Workbench\App\ActionMiddleware\MiddlewareExampleOne;
use Workbench\App\ActionMiddleware\MiddlewareExampleTwo;
use Workbench\App\Actions\ExampleAction;
use Workbench\App\Actions\ExampleActionB;
use Workbench\App\Actions\ExampleActionWithError;
use Workbench\App\Actions\ExampleActionWithFakeHandler;
use Workbench\App\Actions\ExampleActionWithMiddleware;
use Workbench\App\Actions\ExampleActionWithSlowProcess;
use Workbench\App\Actions\ExampleAlternativeActionB;

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

test('events are fired correctly when passing', function () {
    Event::fake();

    Action::dispatch($action = new ExampleAction([ 'foo' => 'bar' ]));

    Event::assertDispatched(fn (ActionDispatching $event) => $event->action === $action);
    Event::assertDispatched(fn (ActionDispatched $event) => $event->action === $action);
});

test('events are fired correctly when failing', function () {
    Event::fake();

    $action = new ExampleActionWithError([]);
    expect(fn () => Action::dispatch($action))
        ->toThrow(InvalidArgumentException::class, 'This is a test exception');

    Event::assertDispatched(fn (ActionDispatching $event) => $event->action === $action);
    Event::assertDispatched(fn (ActionFailed $event) => $event->action === $action);
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
    expect($times->last()->asMilliseconds())->toBeGreaterThan(1);
});

test('actions can pipe through middleware', function () {
    MiddlewareExample::$history = [];

    $action = new ExampleActionWithMiddleware([
        'foo' => 'bar',
    ]);
    $result = Action::dispatch($action);

    expect($result)->toBe(1234);
    expect(MiddlewareExample::$history)->toBe([
        [MiddlewareExampleOne::class, $action],
        [MiddlewareExampleTwo::class, $action],
        [ExampleActionWithMiddleware::class, $action],
    ]);
});

test('actions can pipe through middleware and return early', function () {
    MiddlewareExample::$history = [];

    $action = new ExampleActionWithMiddleware([
        'foo' => 'stop', // this will trigger it to stop after the first middleware
    ]);
    $result = Action::dispatch($action);

    expect($result)->toBe(5678);
    expect(MiddlewareExample::$history)->toBe([
        [MiddlewareExampleOne::class, $action],
    ]);
});

test('actions can be replaced', function () {
    ExampleActionB::$ran = [];
    ExampleAlternativeActionB::$ran = [];

    ExampleActionB::dispatch(1);
    expect(ExampleActionB::$ran)->toBe([1]);
    ExampleAlternativeActionB::dispatch(2);
    expect(ExampleAlternativeActionB::$ran)->toBe([2]);

    Action::replace([
        ExampleActionB::class => ExampleAlternativeActionB::class,
    ]);

    ExampleActionB::dispatch(3);
    expect(ExampleActionB::$ran)->toBe([1]);
    expect(ExampleAlternativeActionB::$ran)->toBe([2, 3]);
});
