# Laravel Actions

A simple yet flexible implementation of Actions in Laravel.

![Static Analysis](https://github.com/bradietilley/laravel-actions/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/laravel-actions/actions/workflows/tests.yml/badge.svg)
![Laravel Version](https://img.shields.io/badge/Laravel%20Version-11.28-F9322C)
![PHP Version](https://img.shields.io/badge/PHP%20Version-8.3-4F5B93)

## Introduction

Actions are compartmentalised bits of code that perform an... Action. This package provides a one-stop shop for how to define your application's actions. Many developers believe that actions "should not" be defined as methods in your `Model` (such as `$user->assignDefaultRole()`), nor should they be in standalone `Job` classess (`App\Jobs\Users\AssignDefaultRole`). This is where this package comes in to play.

In this package, Actions are built similar to synchronously dispatched Jobs. Such as there's a dispatcher that dispatches the action synchronously, just like with jobs, and there's also even a Facade to enable faking of the actions, just like with jobs.

The separation from `Bus` is crucial for sanity in larger projects where you have a huge amount of jobs and actions. Plus it just makes sense. Just like how you wouldn't want `Event::fake()` to fake the `Bus` classes (jobs), you wouldn't want `Bus::fake()` to fake your `Action` classes. Or maybe you do. Up to you. Either way...


## Installation

```
composer require bradietilley/laravel-actions
```

## Documentation

First, brush up on your `Bus` knowledge (i.e. Jobs). Because this is pretty much a standalone copy of how (sychronously) dispatched jobs operate in conjunction with the `Bus::fake()` and `Bus::assert*()` methods.

### Actions → The `Action` class

First and foremost, the equivalent to a job is a class that implements the `BradieTilley\Actions\Contracts\Actionable` interface. An `Actionable` class in one that has a `handle` method (like a job) and one that can be dispatched (like a job). The `Actionable` interface doesn't provide any method signature to allow for full customisation and dependency injection (to workaround a limitation of PHP).

Creating an `Actionable` class is easy. The easiest way would be to extend the `BradieTilley\Actions\Action` abstract class which has the boilerplate you need. Alternatively, implement the `Actionable` interface and add in the `BradieTilley\Actions\Dispatchable` trait.

Here's a rudimentary example of an action, using both of the aforementioned approaches:

```php
/**
 * Using the Action class
 */
class AssignDefaultRole extends \BradieTilley\Actions\Action
{
    public function __construct(public readonly User $user)
    {}

    public function handle(): User
    {
        if ($this->user->role) {
            return $this->user->role;
        }

        $this->user->update([
            'role' => $default => Role::DEFAULT,
        ]);

        return $default;
    }
}

/**
 * Using the Actionable interface and Dispatchable trait.
 */
class AssignDefaultRole implements \BradieTilley\Actions\Contracts\Actionable
{
    use \BradieTilley\Actions\Dispatchable;

    public function __construct(public readonly User $user)
    {}

    public function handle(): User
    {
        if ($this->user->role) {
            return $this->user->role;
        }

        $this->user->update([
            'role' => $default => Role::DEFAULT,
        ]);

        return $default;
    }
}
```

### Actions → The `Action` facade

A facade has been made available using the `BradieTilley\Actions\Facades\Action` class.

You can dispatch actions using the facade, such as

```php
$role = Action::dispatch(new AssignDefaultRole($user));
```

However you can also avoid the facade entirely by using the dispatch method (probably more preferred.):

```php
$role = AssignDefaultRole::dispatch($user);
```

### Actions → Replacing Actions

You can also replace actions - whether you're in a test environment or you just want some complex on-the-fly action swapping. Simply use the facade's `replace()` method to replace any actions with other actions. A replacement action should share a similar signature to the replaced action, such as having the same arguments and return type, but is not enforced.

```php
Action::replace(ExampleActionA::class, ExampleActionB::class);

ExampleActionA::dispatch($user, '123');
// Runs ExampleActionB with $user and '123' constructor arguments instead of ExampleActionA
```

You can also replace multiple at once:

```php
Action::replace([
    // replace an action from a package and use your own action
    DownloadAvatarFromUrl::class => CustomDownloadAvatarFromUrl::class,
    // temporarily disable an integration
    SynchroniseMemberToMailchimp::class => CustomEmptyAction::class,
]);
```

### Testing → Faking

The `Action` facade wraps the underlying `Dispatcher`, which can be swapped out for a `FakeDispatcher` that tracks all Actions that have been dispatched, just like the `Bus` Dispatcher does with jobs.

An example of this is:

```php
use BradieTilley\Actions\Facades\Action;

// your test
Action::fake();

// your app
AssignDefaultRole::dispatch($user);

// your test
Action::assertDispatched(AssignDefaultRole::class); // pass
Action::assertNotDispatched(AssignAdminRole::class); // pass
```

The following methods are supported:

- `Action::assertDispatched()`
- `Action::assertDispatchedTimes()`
- `Action::assertNotDispatched()`
- `Action::assertNothingDispatched()`

These operate exactly like their `Bus` counterpart, so feel free to refer to Laravel's Bus Faking docs for how to use these 4 methods as there may be crossover in functionality.

### Testing → Faking specific actions

You may wish to fake only a subset of actions. This can be achieved via the `Action::fake()` facade call:

```php
use BradieTilley\Actions\Facades\Action;

// your test
Action::fake([
    RecordAuditLog::class,
]);

// your app
AssignDefaultRole::dispatch($user); // still runs
RecordAuditLog::dispatch($user); // doesn't run

// your test
Action::assertDispatched(RecordAuditLog::class); // pass
```

### Testing → Faking all except specific actions

You may wish to fake all actions except a few. This can be achieved via the `except()` method:

```php
use BradieTilley\Actions\Facades\Action;

// your test
Action::fake()->except([
    AssignDefaultRole::class,
]);

// your app
AssignDefaultRole::dispatch($user); // still runs
RecordAuditLog::dispatch($user); // doesn't run

// your test
Action::assertDispatched(RecordAuditLog::class); // pass
```

### Testing → Faking and un-faking actions**

Often your test suite will provide a constant list of actions to fake, however for specific tests you might wish to include additional actions to fake. 

```php
Action::fake([
    ExampleA::class,
]);

ExampleA::dispatch(); // will skip
ExampleB::dispatch(); // will run

Action::addFake(ExampleB::class);

ExampleA::dispatch(); // will skip
ExampleB::dispatch(); // will skip

Action::removeFake(ExampleA::class);

ExampleA::dispatch(); // will run
ExampleB::dispatch(); // will skip
```

### Testing → Allowing execution of actions

From experience, this is a common approach. Something that Bus doesn't offer (as far as I know) is to allow for assertions against dispatched jobs but have those jobs *still run*. With actions, just simply allow execution using the following syntax:

```php
use BradieTilley\Actions\Facades\Action;

// your test
Action::fake()->allowExecution();

// your app
AssignDefaultRole::dispatch($user); // still runs
RecordAuditLog::dispatch($user); // still runs

// your test
Action::assertDispatched(RecordAuditLog::class); // pass
```

And then you can turn it off mid-test too:

```php
use BradieTilley\Actions\Facades\Action;

// your test
Action::fake()->disallowExecution();

// your app
AssignDefaultRole::dispatch($user); // doesn't run
RecordAuditLog::dispatch($user); // doesn't run

// your test
Action::assertDispatched(RecordAuditLog::class); // pass
```

### Events → Listening and monitoring action usage

Often you may want to produce reporting, logging, or other event-driven workflows. This can be achieved via Laravel's event architecture.

Immediately before an action is dispatched, it will trigger an event: `BradieTilley\Actions\Events\ActionDispatching`.

The `BradieTilley\Actions\Contracts\Actionable` class is provided in the event under the `action` property.

```php
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

Event::listen(function (ActionDispatching $event) {
    Log::channel('actions')->debug(sprintf(
        'Running action %s',
        $event->action::class,
    ));
});
```

Immediately after an action is dispatched, it will trigger an event: `BradieTilley\Actions\Events\ActionDispatched`.

The `BradieTilley\Actions\Contracts\Actionable` class is provided in the event under the `action` property.

A summary of the time it took to execute the action (`SebastianBergmann\Timer\Duration` class) is provided in the event under the `duration` property.

```php
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

Event::listen(function (ActionDispatched $event) {
    Log::channel('actions')->debug(sprintf(
        'Successfuly ran action %s in %s milliseconds',
        $event->action::class,
        $event->duration->asMilliseconds(),
    ));
});
```

When an action throws a `Throwable` error/exception, it will trigger an event: `BradieTilley\Actions\Events\ActionFailed`.

The `BradieTilley\Actions\Contracts\Actionable` class is provided in the event under the `action` property.

The exception (instance of `Throwable`) class is provided in the event under the `error` property.

```php
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

Event::listen(function (ActionFailed $event) {
    Log::channel('actions')->debug(sprintf(
        'Failed to run action %s with error %s (see sentry)',
        $event->action::class,
        $event->error->getMessage(),
    ));
});
```

## Author

- [Bradie Tilley](https://github.com/bradietilley)
