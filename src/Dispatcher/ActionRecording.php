<?php

namespace BradieTilley\Actions\Dispatcher;

use BradieTilley\Actions\Contracts\Actionable;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ReflectsClosures;
use PHPUnit\Framework\Assert as PHPUnit;

trait ActionRecording
{
    use ReflectsClosures;

    protected bool $recording = false;

    /**
     * List of actions that have run this session
     *
     * @var array<class-string<Actionable>,array<Actionable>>
     */
    protected array $actions = [];

    /**
     * Assert if a job was dispatched based on a truth-test callback.
     */
    public function assertDispatched(Closure|string $command, Closure|int|null $callback = null): static
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
            /** @var class-string<Actionable> $command */
            /** @var Closure $callback */
        }

        if (is_int($callback)) {
            return $this->assertDispatchedTimes($command, $callback);
        }

        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->count() > 0,
            "The expected [{$command}] job was not dispatched.",
        );

        return $this;
    }

    /**
     * Assert if a job was pushed a number of times.
     */
    public function assertDispatchedTimes(Closure|string $command, int $times = 1): static
    {
        $callback = null;

        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
            /** @var class-string<Actionable> $command */
            /** @var Closure $callback */
        }

        $count = $this->dispatched($command, $callback)->count();

        PHPUnit::assertSame(
            $times,
            $count,
            "The expected [{$command}] action was dispatched {$count} times instead of {$times} times.",
        );

        return $this;
    }

    /**
     * Determine if a job was dispatched based on a truth-test callback.
     */
    public function assertNotDispatched(Closure|string $command, ?Closure $callback = null): static
    {
        if ($command instanceof Closure) {
            [$command, $callback] = [$this->firstClosureParameterType($command), $command];
            /** @var class-string<Actionable> $command */
            /** @var Closure $callback */
        }

        PHPUnit::assertTrue(
            $this->dispatched($command, $callback)->count() === 0,
            "The unexpected [{$command}] action was dispatched.",
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
     *
     * @return Collection<int, Actionable>
     */
    public function dispatched(string $command, ?Closure $callback = null): Collection
    {
        if (! $this->hasDispatched($command)) {
            return Collection::make();
        }

        $callback = $callback ?: fn () => true;

        return Collection::make($this->actions[$command])
            ->filter(fn (Actionable $action) => $callback($action));
    }

    /**
     * Determine if there are any stored commands for a given class.
     */
    public function hasDispatched(string $action): bool
    {
        return isset($this->actions[$action]) && ! empty($this->actions[$action]);
    }

    public function enableRecording(): static
    {
        $this->recording = true;

        return $this;
    }

    public function disableRecording(): static
    {
        $this->recording = false;

        return $this;
    }

    public function resetRecordings(): static
    {
        $this->actions = [];

        return $this;
    }

    public function recordAction(Actionable $action): void
    {
        if (! $this->recording) {
            return;
        }

        $this->actions[$action::class][] = $action;
    }
}
