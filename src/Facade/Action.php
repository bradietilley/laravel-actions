<?php

namespace BradieTilley\Actions\Facade;

use BradieTilley\Actions\Contracts\Dispatcher as DispatcherContract;
use BradieTilley\Actions\Dispatcher\FakeDispatcher;
use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed dispatch(mixed $command)
 * @method static BradieTilley\Actions\Dispatcher\FakeDispatcher except(array|string $jobsToDispatch)
 * @method static void assertDispatched(string|\Closure $command, callable|int|null $callback = null)
 * @method static void assertDispatchedTimes(string|\Closure $command, int $times = 1)
 * @method static void assertNotDispatched(string|\Closure $command, callable|null $callback = null)
 * @method static void assertNothingDispatched()
 * @method static \Illuminate\Support\Collection dispatched(string $command, callable|null $callback = null)
 * @method static bool hasDispatched(string $command)
 *
 * @see \BradieTilley\Actions\Dispatcher\Dispatcher
 * @see \BradieTilley\Actions\Dispatcher\FakeDispatcher
 */
class Action extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @param class-string|array<int, class-string|(Closure(\BradieTilley\Actions\Contracts\Action $action): bool)>|(Closure(\BradieTilley\Actions\Contracts\Action $action): bool) $actionsToFake
     */
    public static function fake(array|string|Closure $actionsToFake = []): FakeDispatcher
    {
        $fake = new FakeDispatcher($actionsToFake, self::$app);

        static::swap($fake);

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
