<?php

use BradieTilley\Actions\Contracts\Dispatcher;
use Workbench\App\Actions\ExampleAction;

test('the dispatcher class can run actions', function () {
    $action = new ExampleAction([ 'foo' => 'bar' ]);

    /** @var Dispatcher $dispatcher */
    $dispatcher = app(Dispatcher::class);

    $result = $dispatcher->dispatch($action);

    expect($result)->toBe([
        'foo' => 'bar',
    ]);
});
