<?php

use BradieTilley\Actions\Facade\Action as Facade;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\Fixtures\ExampleAction;
use Tests\Fixtures\ExampleActionWithFakeHandler;

beforeEach(function () {
    Facade::fake();
});

test('the assertDispatched works with an action class name', function () {
    ExampleActionWithFakeHandler::dispatch([]);

    /**
     * By default, the assertion will fail
     */
    expect(fn () => Facade::assertDispatched(ExampleAction::class))
        ->toThrow(ExpectationFailedException::class);

    /**
     * The assertion will pass when that action has been dispatched
     */
    ExampleAction::dispatch([]);

    expect(fn () => Facade::assertDispatched(ExampleAction::class))
        ->not->toThrow(ExpectationFailedException::class);

    /**
     * After re-faking the Action class, the assertion will fail
     */
    Facade::fake();

    expect(fn () => Facade::assertDispatched(ExampleAction::class))
        ->toThrow(ExpectationFailedException::class);
});

test('the assertDispatched works with a callback', function () {
    ExampleActionWithFakeHandler::dispatch([]);
    $expect = [ '4' ];

    /**
     * By default, the assertion will fail
     */
    expect(fn () => Facade::assertDispatched(fn (ExampleAction $action) => $action->value === $expect))
        ->toThrow(ExpectationFailedException::class);

    /**
     * The assertion will fail when an action is dispatched that fails the callback
     */
    ExampleAction::dispatch([]);

    expect(fn () => Facade::assertDispatched(fn (ExampleAction $action) => $action->value === $expect))
        ->toThrow(ExpectationFailedException::class);

    /**
     * The assertion will pass when an action is dispatched that passes the callback
     */
    ExampleAction::dispatch($expect);

    expect(fn () => Facade::assertDispatched(fn (ExampleAction $action) => $action->value === $expect))
        ->not->toThrow(ExpectationFailedException::class);

    /**
     * After re-faking the Action class, the assertion will fail
     */
    Facade::fake();

    expect(fn () => Facade::assertDispatched(fn (ExampleAction $action) => $action->value === $expect))
        ->toThrow(ExpectationFailedException::class);
});

test('the assertDispatched works with an action class name and callback', function () {
    ExampleActionWithFakeHandler::dispatch([]);
    $expect = [ '4' ];

    /**
     * By default, the assertion will fail
     */
    expect(fn () => Facade::assertDispatched(ExampleAction::class, fn (ExampleAction $action) => $action->value === $expect))
        ->toThrow(ExpectationFailedException::class);

    /**
     * The assertion will fail when an action is dispatched that fails the callback
     */
    ExampleAction::dispatch([]);

    expect(fn () => Facade::assertDispatched(ExampleAction::class, fn (ExampleAction $action) => $action->value === $expect))
        ->toThrow(ExpectationFailedException::class);

    /**
     * The assertion will pass when an action is dispatched that passes the callback
     */
    ExampleAction::dispatch($expect);

    expect(fn () => Facade::assertDispatched(ExampleAction::class, fn (ExampleAction $action) => $action->value === $expect))
        ->not->toThrow(ExpectationFailedException::class);

    /**
     * After re-faking the Action class, the assertion will fail
     */
    Facade::fake();

    expect(fn () => Facade::assertDispatched(ExampleAction::class, fn (ExampleAction $action) => $action->value === $expect))
        ->toThrow(ExpectationFailedException::class);
});

test('the assertDispatchedTimes works with an action class name', function () {
    ExampleActionWithFakeHandler::dispatch([]);

    /**
     * By default the assertion will pass if you pass in 0
     */
    expect(fn () => Facade::assertDispatchedTimes(ExampleAction::class, 0))
        ->not->toThrow(ExpectationFailedException::class);

    /**
     * By default the assertion will fail if you pass in 1
     */
    expect(fn () => Facade::assertDispatchedTimes(ExampleAction::class, 1))
        ->toThrow(ExpectationFailedException::class);

    foreach (range(1, 5) as $time) {
        ExampleAction::dispatch([]);

        /**
         * After running, the assertion will fail if you pass in the correct times
         */
        expect(fn () => Facade::assertDispatchedTimes(ExampleAction::class, $time))
            ->not->toThrow(ExpectationFailedException::class);

        /**
         * After running, the assertion will fail if you pass in an incorrect times
         */
        expect(fn () => Facade::assertDispatchedTimes(ExampleAction::class, 0))
            ->toThrow(ExpectationFailedException::class);

        /**
         * After running, the assertion will fail if you pass in an incorrect times
         */
        expect(fn () => Facade::assertDispatchedTimes(ExampleAction::class, $time - 1))
            ->toThrow(ExpectationFailedException::class);

        /**
         * After running, the assertion will fail if you pass in an incorrect times
         */
        expect(fn () => Facade::assertDispatchedTimes(ExampleAction::class, $time + 1))
            ->toThrow(ExpectationFailedException::class);
    }

    /**
     * It can also be run via the assertDispatched() method
     */
    expect(Facade::assertDispatched(ExampleAction::class, 5))
        ->not->toThrow(ExpectationFailedException::class);
});

test('the assertDispatchedTimes works with a callback', function () {
    ExampleActionWithFakeHandler::dispatch([]);
    $expect = ['c'];

    /**
     * By default the assertion will pass if you pass in 0
     */
    expect(fn () => Facade::assertDispatchedTimes(fn (ExampleAction $action) => $action->value === $expect, 0))
        ->not->toThrow(ExpectationFailedException::class);

    /**
     * By default the assertion will fail if you pass in 1
     */
    expect(fn () => Facade::assertDispatchedTimes(fn (ExampleAction $action) => $action->value === $expect, 1))
        ->toThrow(ExpectationFailedException::class);

    foreach (range(1, 5) as $time) {
        ExampleAction::dispatch($expect);

        /**
         * After running, the assertion will pass if you pass in the correct times
         */
        expect(fn () => Facade::assertDispatchedTimes(fn (ExampleAction $action) => $action->value === $expect, $time))
            ->not->toThrow(ExpectationFailedException::class);

        /**
         * After running, the assertion will fail if the callback returns false
         */
        expect(fn () => Facade::assertDispatchedTimes(fn (ExampleAction $action) => $action->value === ['nope'], $time))
            ->toThrow(ExpectationFailedException::class);

        /**
         * After running, the assertion will fail if you pass in an incorrect times
         */
        expect(fn () => Facade::assertDispatchedTimes(fn (ExampleAction $action) => $action->value === $expect, 0))
            ->toThrow(ExpectationFailedException::class);

        /**
         * After running, the assertion will fail if you pass in an incorrect times
         */
        expect(fn () => Facade::assertDispatchedTimes(fn (ExampleAction $action) => $action->value === $expect, $time - 1))
            ->toThrow(ExpectationFailedException::class);

        /**
         * After running, the assertion will fail if you pass in an incorrect times
         */
        expect(fn () => Facade::assertDispatchedTimes(fn (ExampleAction $action) => $action->value === $expect, $time + 1))
            ->toThrow(ExpectationFailedException::class);
    }

    /**
     * It can also be run via the assertDispatched() method
     */
    expect(Facade::assertDispatched(fn (ExampleAction $action) => $action->value === $expect, 5))
        ->not->toThrow(ExpectationFailedException::class);
});

test('the assertNotDispatched works with an action class name', function () {
    ExampleActionWithFakeHandler::dispatch([]);

    /**
     * By default the assertion will pass as it hasn't run yet
     */
    expect(fn () => Facade::assertNotDispatched(ExampleAction::class))
        ->not->toThrow(ExpectationFailedException::class);


    /**
     * After dispatching the action, the assertion will fail
     */
    ExampleAction::dispatch([]);
    expect(fn () => Facade::assertNotDispatched(ExampleAction::class))
        ->toThrow(ExpectationFailedException::class);
});

test('the assertNotDispatched works with a callback', function () {
    $expect = ['9'];
    ExampleActionWithFakeHandler::dispatch($expect);

    /**
     * By default the assertion will pass as it hasn't run yet
     */
    expect(fn () => Facade::assertNotDispatched(fn (ExampleAction $action) => $action->value === $expect))
        ->not->toThrow(ExpectationFailedException::class);

    /**
     * After dispatching the action, the assertion will fail
     */
    ExampleAction::dispatch($expect);
    expect(fn () => Facade::assertNotDispatched(fn (ExampleAction $action) => $action->value === $expect))
        ->toThrow(ExpectationFailedException::class);

    /**
     * The assertion will pass if the closure returns false
     */
    ExampleAction::dispatch($expect);
    expect(fn () => Facade::assertNotDispatched(fn (ExampleAction $action) => $action->value === ['something']))
        ->not->toThrow(ExpectationFailedException::class);
});

test('the assertNothingDispatched works', function () {
    /**
     * By default the assertion will pass
     */
    expect(fn () => Facade::assertNothingDispatched())
        ->not->toThrow(ExpectationFailedException::class);

    ExampleAction::dispatch([]);

    /**
     * Once any action is dispatched the assertion will fail
     */
    expect(fn () => Facade::assertNothingDispatched())
        ->toThrow(ExpectationFailedException::class);
});
