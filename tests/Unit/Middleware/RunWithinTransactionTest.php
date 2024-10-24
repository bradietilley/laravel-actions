<?php

use BradieTilley\Actions\Action;
use BradieTilley\Actions\Facades\Action as FacadesAction;
use BradieTilley\Actions\Middleware\RunWithinTransaction;
use Workbench\App\Models\User;

class RunWithinTransactionHelper
{
    public static bool $error = false;
}

test('actions can run within a DB transaction', function (bool $error) {
    RunWithinTransactionHelper::$error = $error;

    $action = new class () extends Action {
        public function middleware(): array
        {
            return [
                RunWithinTransaction::class,
            ];
        }

        public function handle()
        {
            $user = User::create([
                'email' => 'foobar@example.org',
                'name' => 'Test',
                'password' => '',
            ]);

            if (RunWithinTransactionHelper::$error) {
                throw new Exception('This is intentional');
            }

            return $user;
        }
    };

    $run = fn (): User|null => FacadesAction::dispatch($action);

    if ($error) {
        expect($run)->toThrow(Exception::class, 'This is intentional');
        expect(User::where('email', 'foobar@example.org')->exists())->toBe(false);
    } else {
        $user = $run();

        expect($user?->email)->toBe('foobar@example.org');
        expect(User::where('email', 'foobar@example.org')->exists())->toBe(true);
    }
})->with([
    true,
    false,
]);
