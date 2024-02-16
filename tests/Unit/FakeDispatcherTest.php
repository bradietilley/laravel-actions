<?php

use BradieTilley\Actions\Contracts\Actionable;
use BradieTilley\Actions\Contracts\Dispatcher as ContractsDispatcher;
use BradieTilley\Actions\Dispatcher\Dispatcher;
use BradieTilley\Actions\Dispatcher\FakeDispatcher;
use BradieTilley\Actions\Facade\Action as Facade;
use Illuminate\Support\Collection;
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

test('a FakeDispatcher use a closure to determine if an action should be faked', function () {
    $all = Collection::make();

    $fake = Facade::fake(function (Actionable $action) use ($all) {
        $all->push($action->value);

        return $action->value['fake'] === true;
    });

    $shouldRun = fn (Actionable $action) => invokeProtectedMethod($fake, 'shouldFakeJob', $action);

    expect($shouldRun(new ExampleAction([ 'fake' => true ])))->toBe(true);
    expect($shouldRun(new ExampleAction([ 'fake' => false ])))->toBe(false);

    expect($shouldRun(new ExampleActionWithFakeHandler([ 'fake' => 1 ])))->toBe(false);
    expect($shouldRun(new ExampleActionWithFakeHandler([ 'fake' => 0 ])))->toBe(false);
    expect($shouldRun(new ExampleActionWithFakeHandler([ 'fake' => true ])))->toBe(true);

    expect($all->all())->toBe([
        ['fake' => true],
        ['fake' => false],
        ['fake' => 1],
        ['fake' => 0],
        ['fake' => true],
    ]);
});

test('a FakeDispatcher use a closure to determine if an action should be dispatched', function () {
    $all = Collection::make();

    $fake = Facade::fake()->except(function (Actionable $action) use ($all) {
        $all->push($action->value);

        return $action->value['run'] === true;
    });

    $shouldRun = fn (Actionable $action) => invokeProtectedMethod($fake, 'shouldDispatchCommand', $action);

    expect($shouldRun(new ExampleAction([ 'run' => true ])))->toBe(true);
    expect($shouldRun(new ExampleAction([ 'run' => false ])))->toBe(false);

    expect($shouldRun(new ExampleActionWithFakeHandler([ 'run' => 1 ])))->toBe(false);
    expect($shouldRun(new ExampleActionWithFakeHandler([ 'run' => 0 ])))->toBe(false);
    expect($shouldRun(new ExampleActionWithFakeHandler([ 'run' => true ])))->toBe(true);

    expect($all->all())->toBe([
        ['run' => true],
        ['run' => false],
        ['run' => 1],
        ['run' => 0],
        ['run' => true],
    ]);
});
