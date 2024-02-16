<?php

namespace BradieTilley\Actionables\Dispatcher;

use BradieTilley\Actionables\Contracts\Action;
use BradieTilley\Actionables\Contracts\ActionFake;
use BradieTilley\Actionables\Dispatcher\Dispatcher as ActualDispatcher;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ReflectsClosures;
use PHPUnit\Framework\Assert as PHPUnit;

class FakeDispatcher extends ActualDispatcher
{
    use ReflectsClosures;

    /**
     * List of actions to fake
     *
     * @var array<int, class-string|(Closure(\BradieTilley\Actionables\Contracts\Action $action): bool)>
     */
    protected array $actionsToFake = [];

    /**
     * List of actions to dispatch
     *
     * @var array<int, class-string|(Closure(\BradieTilley\Actionables\Contracts\Action $action): bool)>
     */
    protected array $actionsToDispatch = [];

    /**
     * List of actions that have run this session
     *
     * @var array<class-string, array<int, Action>>
     */
    protected array $actions = [];

    /**
     * Flag to determine if the actions should still execute
     */
    protected bool $executeActions = false;

    /**
     * @param class-string|array<int, class-string|(Closure(\BradieTilley\Actionables\Contracts\Action $action): bool)>|(Closure(\BradieTilley\Actionables\Contracts\Action $action): bool) $actionsToFake
     */
    public function __construct(array|string|Closure $actionsToFake, Container $container)
    {
        parent::__construct($container);
        $this->actionsToFake = Arr::wrap($actionsToFake);
    }

    /**
     * Specify the jobs that should be dispatched instead of faked.
     *
     * @param class-string|array<int, class-string|(Closure(\BradieTilley\Actionables\Contracts\Action $action): bool)>|(Closure(\BradieTilley\Actionables\Contracts\Action $action): bool) $actionsToDispatch
     */
    public function except(array|string|Closure $actionsToDispatch): static
    {
        $this->actionsToDispatch = array_merge($this->actionsToDispatch, Arr::wrap($actionsToDispatch));

        return $this;
    }

    /**
     * Allow all faked actions to run, turning this FakeDispatcher
     * into a mere logger of what was run.
     */
    public function allowExecution(): static
    {
        $this->executeActions = true;

        return $this;
    }

    /**
     * Disallow all faked actions to run, for optimal execution
     * of tests.
     */
    public function disallowExecution(): static
    {
        $this->executeActions = false;

        return $this;
    }

    /**
     * Dispatch the given action
     */
    public function dispatch(Action $action): mixed
    {
        if (! $this->shouldFakeJob($action)) {
            return parent::dispatch($action);
        }

        $this->actions[get_class($action)][] = $action;

        if ($this->executeActions) {
            return parent::dispatch($action);
        }

        return $action instanceof ActionFake ? $action->handleFake() : null;
    }

    /**
     * Determine if an action should be faked or actually dispatched.
     */
    protected function shouldFakeJob(Action $action): bool
    {
        if ($this->shouldDispatchCommand($action)) {
            return false;
        }

        if (empty($this->actionsToFake)) {
            return true;
        }

        return Collection::make($this->actionsToFake)
            ->filter(function (Closure|string $job) use ($action) {
                return $job instanceof Closure
                    ? $job($action)
                    : $job === $action::class;
            })
            ->isNotEmpty();
    }

    /**
     * Determine if a command should be dispatched or not.
     */
    protected function shouldDispatchCommand(Action $action): bool
    {
        return Collection::make($this->actionsToDispatch)
            ->filter(function (Closure|string $job) use ($action) {
                return $job instanceof Closure
                    ? $job($action)
                    : $job === $action::class;
            })
            ->isNotEmpty();
    }

    /**
     * Assert if a job was dispatched based on a truth-test callback.
     */
    public function assertDispatched(string|Closure $command, Closure|int|null $callback = null): static
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
            /** @var class-string $command */
            /** @var Closure $callback */
        }

        if (is_int($callback)) {
            return $this->assertDispatchedTimes($command, $callback);
        }

        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->count() > 0,
            "The expected [{$command}] job was not dispatched."
        );

        return $this;
    }

    /**
     * Assert if a job was pushed a number of times.
     */
    public function assertDispatchedTimes(string|Closure $command, int $times = 1): static
    {
        $callback = null;

        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
            /** @var class-string $command */
            /** @var Closure $callback */
        }

        $count = $this->dispatched($command, $callback)->count();

        PHPUnit::assertSame(
            $times,
            $count,
            "The expected [{$command}] action was dispatched {$count} times instead of {$times} times."
        );

        return $this;
    }

    /**
     * Determine if a job was dispatched based on a truth-test callback.
     */
    public function assertNotDispatched(string|Closure $command, Closure $callback = null): static
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
            /** @var class-string $command */
            /** @var Closure $callback */
        }

        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->count() === 0,
            "The unexpected [{$command}] action was dispatched."
        );

        return $this;
    }

    /**
     * Assert that no jobs were dispatched.
     */
    public function assertNothingDispatched(): static
    {
        PHPUnit::assertEmpty($this->actions, 'Actions were dispatched unexpectedly.');

        return $this;
    }

    /**
     * Get all of the jobs matching a truth-test callback.
     */
    public function dispatched(string $command, ?Closure $callback = null): Collection
    {
        if (! $this->hasDispatched($command)) {
            return Collection::make();
        }

        $callback = $callback ?: fn () => true;

        return Collection::make($this->actions[$command])
            ->filter(fn (Action $action) => $callback($action));
    }

    /**
     * Determine if there are any stored commands for a given class.
     */
    public function hasDispatched(string $action): bool
    {
        return isset($this->actions[$action]) && ! empty($this->actions[$action]);
    }
}
