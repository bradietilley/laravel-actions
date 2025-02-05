<?php

use BradieTilley\Actions\Facades\Action;
use Workbench\App\Actions\ExampleActionWithFakeHandler;
use Workbench\App\Actions\ExampleActionWithFakeHandler2;

beforeEach(function () {
    $this->assertRunsHandleFake = function (string $action) {
        $value = $action::dispatch([
            'foo' => 'real',
        ]);

        expect($value)->toBe([
            'foo' => 'faked',
        ]);
    };

    $this->assertRunsHandleReal = function (string $action) {
        $value = $action::dispatch([
            'foo' => 'real',
        ]);

        expect($value)->toBe([
            'foo' => 'real',
        ]);
    };
});

test('when actions are faked they will run handleFake', function () {
    Action::fake();

    ($this->assertRunsHandleFake)(ExampleActionWithFakeHandler::class);
    ($this->assertRunsHandleFake)(ExampleActionWithFakeHandler2::class);
});

test('when actions are faked they will run handle if allowed execution', function () {
    Action::fake()->allowExecution();

    ($this->assertRunsHandleReal)(ExampleActionWithFakeHandler::class);
    ($this->assertRunsHandleReal)(ExampleActionWithFakeHandler2::class);
});

test('an action can be individually faked and individually allow execution', function () {
    Action::fake()->allow(ExampleActionWithFakeHandler::class);

    ($this->assertRunsHandleReal)(ExampleActionWithFakeHandler::class);
    ($this->assertRunsHandleFake)(ExampleActionWithFakeHandler2::class);
});

test('an action can be individually faked and individually disallow execution', function () {
    Action::fake()->allowExecution()->disallow(ExampleActionWithFakeHandler::class);

    ($this->assertRunsHandleFake)(ExampleActionWithFakeHandler::class);
    ($this->assertRunsHandleReal)(ExampleActionWithFakeHandler2::class);
});
