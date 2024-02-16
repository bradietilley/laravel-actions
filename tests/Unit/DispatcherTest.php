<?php

use BradieTilley\Actionables\Contracts\Dispatcher;
use Tests\Fixtures\ExampleAction;

test('the dispatcher class can run actions', function () {
    $action = new ExampleAction([ 'foo' => 'bar' ]);

    /** @var Dispatcher $dispatcher */
    $dispatcher = app(Dispatcher::class);

    $result = $dispatcher->dispatch($action);

    expect($result)->toBe([
        'foo' => 'bar',
    ]);
});
