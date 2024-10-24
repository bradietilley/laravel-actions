<?php

use BradieTilley\Actions\Action;
use BradieTilley\Actions\Facades\Action as FacadesAction;
use BradieTilley\Actions\Middleware\RunWithoutExceptions;

class RunWithoutExceptionsHelper
{
    public static array $history = [];
}

test('actions can run without throwing exceptions', function () {
    $action = new class () extends Action {
        public function middleware(): array
        {
            return [
                RunWithoutExceptions::class,
            ];
        }

        public function handle()
        {
            $exception = new Exception('This is intentional');

            RunWithoutExceptionsHelper::$history[] = $exception;

            throw $exception;
        }
    };

    // No try/catch needed
    FacadesAction::dispatch($action);

    expect(RunWithoutExceptionsHelper::$history)->toHaveCount(1);
});
