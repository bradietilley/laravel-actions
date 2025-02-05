<?php

namespace BradieTilley\Actions\Dispatcher;

use BradieTilley\Actions\Contracts\Actionable;
use BradieTilley\Actions\Contracts\IsFakeable;
use BradieTilley\Actions\Dispatcher\Dispatcher as ActualDispatcher;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Testing\Fakes\Fake;

class FakeDispatcher extends ActualDispatcher implements Fake
{
    use ActionRecording;

    /**
     * List of actions to fake
     *
     * @var array<class-string<Actionable>|(Closure(Actionable): bool)>
     */
    protected array $actionsToFake = [];

    /**
     * List of actions to dispatch
     *
     * @var array<class-string<Actionable>|(Closure(Actionable): bool)>
     */
    protected array $actionsToDispatch = [];

    /**
     * List of actions to allow execution even if faked
     *
     * @var array<class-string<Actionable>, bool>
     */
    protected array $actionsAllowed = [];

    /**
     * Flag to determine if the actions should still execute
     */
    protected bool $executeActions = false;

    /**
     * @param class-string<Actionable>|array<class-string<Actionable>|(Closure(Actionable $action): bool)>|(Closure(Actionable $action): bool) $actionsToFake
     */
    public function __construct(
        array|Closure|string $actionsToFake,
        Container $container,
        EventDispatcher $events,
        DatabaseManager $db,
    ) {
        parent::__construct($container, $events, $db);
        $this->actionsToFake = Arr::wrap($actionsToFake);
        $this->enableRecording();
    }

    /**
     * Specify the actions that should be faked
     *
     * @param class-string<Actionable>|array<class-string<Actionable>|(Closure(Actionable $action): bool)>|(Closure(Actionable $action): bool) $actionsToFake
     */
    public function addFake(array|Closure|string $actionsToFake): static
    {
        return $this->with($actionsToFake);
    }

    /**
     * Specify the actions that should be faked
     *
     * @param class-string<Actionable>|array<class-string<Actionable>|(Closure(Actionable $action): bool)>|(Closure(Actionable $action): bool) $actionsToFake
     */
    public function with(array|Closure|string $actionsToFake): static
    {
        $this->actionsToFake = array_merge($this->actionsToFake, Arr::wrap($actionsToFake));

        return $this;
    }

    /**
     * Specify the actions that should be dispatched instead of faked.
     *
     * @param class-string<Actionable>|array<class-string<Actionable>|(Closure(Actionable $action): bool)>|(Closure(Actionable $action): bool) $actionsToDispatch
     */
    public function removeFake(array|Closure|string $actionsToDispatch): static
    {
        return $this->except($actionsToDispatch);
    }

    /**
     * Specify the actions that should be dispatched instead of faked.
     *
     * @param class-string<Actionable>|array<class-string<Actionable>|(Closure(Actionable $action): bool)>|(Closure(Actionable $action): bool) $actionsToDispatch
     */
    public function except(array|Closure|string $actionsToDispatch): static
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

    public function allow(array|string $actions): static
    {
        foreach (Arr::wrap($actions) as $action) {
            $this->actionsAllowed[$action] = true;
        }

        return $this;
    }

    public function disallow(array|string $actions): static
    {
        foreach (Arr::wrap($actions) as $action) {
            $this->actionsAllowed[$action] = false;
        }

        return $this;
    }

    /**
     * Dispatch the given action
     */
    public function dispatch(Actionable $action): mixed
    {
        $this->recordAction($action);

        if (! $this->shouldFakeJob($action)) {
            return parent::dispatch($action);
        }

        if ($this->shouldHandleReal($action)) {
            return parent::dispatch($action);
        }

        if ($action instanceof IsFakeable) {
            return $action->handleFake();
        }

        return null;
    }

    /**
     * Determine if an action should be faked or actually dispatched.
     */
    protected function shouldFakeJob(Actionable $action): bool
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

    protected function shouldHandleReal(Actionable $action): bool
    {
        /**
         * If this action is allowed to run
         */
        $explicit = $this->actionsAllowed[$action::class] ?? null;

        if ($explicit !== null) {
            return $explicit;
        }

        /**
         * If all execution is allowed
         */
        if ($this->executeActions) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a command should be dispatched or not.
     */
    protected function shouldDispatchCommand(Actionable $action): bool
    {
        return Collection::make($this->actionsToDispatch)
            ->filter(function (Closure|string $job) use ($action) {
                return $job instanceof Closure
                    ? $job($action)
                    : $job === $action::class;
            })
            ->isNotEmpty();
    }
}
