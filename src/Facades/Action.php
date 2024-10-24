<?php

namespace BradieTilley\Actions\Facades;

use BradieTilley\Actions\Contracts\Actionable as Actionable;
use BradieTilley\Actions\Contracts\Dispatcher as DispatcherContract;
use BradieTilley\Actions\Dispatcher\FakeDispatcher;
use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed dispatch(Actionable $action)
 * @method static BradieTilley\Actions\Dispatcher\FakeDispatcher except(array|string $actionsToDispatch)
 * @method static BradieTilley\Actions\Dispatcher\FakeDispatcher removeFake(array|string $actionsToDispatch)
 * @method static BradieTilley\Actions\Dispatcher\FakeDispatcher with(array|string $actionsToFake)
 * @method static BradieTilley\Actions\Dispatcher\FakeDispatcher addFake(array|string $actionsToFake)
 * @method static void assertDispatched(string|\Closure $action, callable|int|null $callback = null)
 * @method static void assertDispatchedTimes(string|\Closure $action, int $times = 1)
 * @method static void assertNotDispatched(string|\Closure $action, callable|null $callback = null)
 * @method static void assertNothingDispatched()
 * @method static \Illuminate\Support\Collection dispatched(string $action, callable|null $callback = null)
 * @method static bool hasDispatched(string $action)
 *
 * @method static Dispatcher|FakeDispatcher getFacadeRoot()
 *
 * @see \BradieTilley\Actions\Dispatcher\Dispatcher
 * @see \BradieTilley\Actions\Dispatcher\FakeDispatcher
 */
class Action extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @param class-string|array<class-string|(Closure(Actionable $action): bool)>|(Closure(Actionable $action): bool) $actionsToFake
     */
    public static function fake(array|string|Closure $actionsToFake = []): FakeDispatcher
    {
        $app = app();

        /** @var Dispatcher $events */
        $events = app(Dispatcher::class);

        /** @var DatabaseManager $db */
        $db = app(DatabaseManager::class);

        $fake = new FakeDispatcher($actionsToFake, $app, $events, $db);

        static::swap($fake);

        return $fake;
    }

    /**
     * Define the Action class replacements to use
     *
     * @template TClass
     * @param array<class-string<TClass>, class-string<TClass>> $actions
     */
    public static function replace(array $actions): void
    {
        foreach ($actions as $find => $replace) {
            app()->alias($replace, $find);
        }
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return DispatcherContract::class;
    }
}
