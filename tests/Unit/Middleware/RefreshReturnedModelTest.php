<?php

use BradieTilley\Actions\Action;
use BradieTilley\Actions\Facades\Action as FacadesAction;
use BradieTilley\Actions\Middleware\RefreshReturnedModel;
use Illuminate\Database\Eloquent\Model;

class RefreshReturnedModelHelper
{
    public static array $history = [];
}

test('can automatically return a refreshed model', function () {
    RefreshReturnedModelHelper::$history = [];

    $action = new class () extends Action {
        public function middleware(): array
        {
            return [
                RefreshReturnedModel::class,
            ];
        }

        public function handle()
        {
            return new class () extends Model {
                public function refresh(): static
                {
                    RefreshReturnedModelHelper::$history[] = $this;

                    return $this;
                }
            };
        }
    };

    FacadesAction::dispatch($action);
    expect(RefreshReturnedModelHelper::$history)->toHaveCount(1);

    FacadesAction::dispatch($action);
    expect(RefreshReturnedModelHelper::$history)->toHaveCount(2);
});
