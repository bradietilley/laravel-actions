<?php

namespace BradieTilley\Actionables\Laravel;

use BradieTilley\Actionables\Contracts\Dispatcher as DispatcherContract;
use BradieTilley\Actionables\Dispatcher\FakeDispatcher;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed dispatch(mixed $command)
 * @method static mixed dispatchSync(mixed $command, mixed $handler = null)
 * @method static mixed dispatchNow(mixed $command, mixed $handler = null)
 * @method static \Illuminate\Support\Testing\Fakes\BusFake except(array|string $jobsToDispatch)
 * @method static void assertDispatched(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatched(string|\Closure $command, callable|null $callback = null)
 * @method static void assertNothingDispatched()
 * @method static void assertDispatchedSync(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedSyncTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatchedSync(string|\Closure $command, callable|null $callback = null)
 * @method static \Illuminate\Support\Collection dispatched(string $command, callable|null $callback = null)
 * @method static \Illuminate\Support\Collection dispatchedSync(string $command, callable|null $callback = null)
 * @method static bool hasDispatched(string $command)
 * @method static bool hasDispatchedSync(string $command)
 * @method static \Illuminate\Support\Testing\Fakes\BusFake serializeAndRestore(bool $serializeAndRestore = true)
 *
 * @see \BradieTilley\Actionables\Dispatcher\Dispatcher
 * @see \BradieTilley\Actionables\Dispatcher\FakeDispatcher
 */
class Action extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake(array|string $actionsToFake = []): FakeDispatcher
    {
        static::swap($fake = new FakeDispatcher($actionsToFake, self::$app));

        return $fake;
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return DispatcherContract::class;
    }
}
