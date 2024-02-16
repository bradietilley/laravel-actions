# Laravel Actions

A simple yet flexible implementation of Actions in Laravel.

![Static Analysis](https://github.com/bradietilley/laravel-actions/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/laravel-actions/actions/workflows/tests.yml/badge.svg)


## Introduction

Actions are compartmentalised bits of code that perform an... Action. This is a one-stop shop for how to define your application's actions. Actions "should not" be defined as methods in your `Model`, nor should they be in standalone `Job` classess. This is where this package comes in.

Actions are built similar to (sync) Jobs. There's a dispatcher that dispatches the action synchronously, just like with jobs, and there's also even a Facade to enable faking of the actions, just like with jobs.

The separation from `Bus` is crucial for sanity in larger projects. Plus it just makes sense. Just like how you wouldn't want `Event::fake()` to fake the `Bus`, you wouldn't want `Bus::fake()` to fake your `Action`. Or maybe you do. Up to you. Either way...


## Installation

```
composer require bradietilley/laravel-actions
```


## Documentation

Are you familiar with Laravel Bus (i.e. Jobs)? It's pretty much a standalone copy of how dispatchable jobs operate in conjunction with the `Bus::fake()` and `Bus::assert*()` methods.

The `BradieTilley\Actions\Action` class is a base class provided by the package for all of your action classes to extend from, kind of similar to adding the `Dispatchable` trait (and `ShouldQueue` interface, etc) to your Job classes. But if you'd prefer to not extend a single base class, you can opt to create your own action classes that implement the `BradieTilley\Actions\Contracts\Action` interface.


Just like jobs, you can utilise your constructor to provide context to the action, and ultimately have it all run within the `handle` method.

Example:

```
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

class AssignDefaultRole implements \BradieTilley\Actions\Contracts\Action
{
    use \BradieTilley\Actions\Actionable;

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


**Usage**

Continuing from the above example, you might want to assign a default role to a user that doesn't have one. The syntad would look like:

```php
$role = AssignDefaultRole::dispatch($user);
```

**Testing**

```php
use BradieTilley\Actions\Facade\Action;

Action::fake();

Action::asstNohingDispatched();
Action::asstNotDispatched(AssignDefaultRole::class);

AssignDefaultRole::dispatch($user);

Action::assertDispatched(AssignDefaultRole::class);
Action::assertDispatched(fn (AssignDefaultRole $action) => $action->user->is($user));
Action::assertDispatched(AssignDefaultRole::class, fn (AssignDefaultRole $action) => $action->user->is($user));
Action::assertDispatched(AssignDefaultRole::class, 1);
Action::assertDispatchedTimes(AssignDefaultRole::class, 1);
```


**Testing**



## Author

- [Bradie Tilley](https://github.com/bradietilley)
