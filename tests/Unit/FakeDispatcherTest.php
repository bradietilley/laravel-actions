<?php

use BradieTilley\Actionables\Contracts\Dispatcher as ContractsDispatcher;
use BradieTilley\Actionables\Dispatcher\Dispatcher;
use BradieTilley\Actionables\Dispatcher\FakeDispatcher;
use BradieTilley\Actionables\Facade\Action as Facade;
use Tests\Fixtures\ExampleAction;
use Tests\Fixtures\ExampleActionA;
use Tests\Fixtures\ExampleActionB;
use Tests\Fixtures\ExampleActionC;
use Tests\Fixtures\ExampleActionWithFakeHandler;

test('the fake method returns a fake dispatcher', function () {
    expect(Facade::getFacadeRoot())->toBeInstanceOf(Dispatcher::class);

    expect(Facade::fake())->toBeInstanceOf(FakeDispatcher::class);
});

test('the fake dispatcher can be swapped out which resets the dispatch history', function () {
    Facade::fake();
    $dispatcher = app(ContractsDispatcher::class);
    expect($dispatcher)->toBeInstanceOf(FakeDispatcher::class);

    /**
     * Dispatching will push to the dispatcher
     */
    $dispatcher->dispatch($action1 = new ExampleAction([]));
    expect(getDispatched($dispatcher))->toBe([
        ExampleAction::class => [
            $action1,
        ],
    ]);

    /**
     * Dispatching twice will push to the same dispatcher
     */
    $dispatcher->dispatch($action2 = new ExampleActionWithFakeHandler([]));
    expect(getDispatched($dispatcher))->toBe([
        ExampleAction::class => [
            $action1,
        ],
        ExampleActionWithFakeHandler::class => [
            $action2,
        ],
    ]);

    /**
     * Re-faking will reset everything
     */
    Facade::fake();
    $dispatcher = app(ContractsDispatcher::class);
    expect($dispatcher)->toBeInstanceOf(FakeDispatcher::class);
    expect(getDispatched($dispatcher))->toBe([]);
});

test('the FakeDispatcher can choose actions to fake and those to run', function () {
    /**
     * Fake Action B then assert that it wasn't run
     */
    Facade::fake([
        ExampleActionB::class,
    ]);

    ExampleActionA::$ran = [];
    ExampleActionB::$ran = [];
    ExampleActionC::$ran = [];

    ExampleActionA::dispatch([ 'a' ]);
    ExampleActionB::dispatch([ 'b' ]);
    ExampleActionC::dispatch([ 'c' ]);

    expect([
        'a' => ExampleActionA::$ran,
        'b' => ExampleActionB::$ran,
        'c' => ExampleActionC::$ran,
    ])->toBe([
        'a' => [
            ['a'],
        ],
        'b' => [
            // did not run
        ],
        'c' => [
            ['c'],
        ],
    ]);

    /**
     * Fake Action C and A then assert that they weren't run
     */
    Facade::fake([
        ExampleActionA::class,
        ExampleActionC::class,
    ]);

    ExampleActionA::$ran = [];
    ExampleActionB::$ran = [];
    ExampleActionC::$ran = [];

    ExampleActionA::dispatch([ 'a' ]);
    ExampleActionB::dispatch([ 'b' ]);
    ExampleActionC::dispatch([ 'c' ]);

    expect([
        'a' => ExampleActionA::$ran,
        'b' => ExampleActionB::$ran,
        'c' => ExampleActionC::$ran,
    ])->toBe([
        'a' => [
            // did not run
        ],
        'b' => [
            ['b'],
        ],
        'c' => [
            // did not run
        ],
    ]);

    /**
     * Fake all except Action C then assert that only C was run
     */
    Facade::fake()->except([
        ExampleActionC::class,
    ]);

    ExampleActionA::$ran = [];
    ExampleActionB::$ran = [];
    ExampleActionC::$ran = [];

    ExampleActionA::dispatch([ 'a' ]);
    ExampleActionB::dispatch([ 'b' ]);
    ExampleActionC::dispatch([ 'c' ]);

    expect([
        'a' => ExampleActionA::$ran,
        'b' => ExampleActionB::$ran,
        'c' => ExampleActionC::$ran,
    ])->toBe([
        'a' => [
            // did not run
        ],
        'b' => [
            // did not run
        ],
        'c' => [
            ['c'],
        ],
    ]);
});
