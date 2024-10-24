<?php

namespace Workbench\App\Actions;

use BradieTilley\Actions\Action;
use Workbench\App\ActionMiddleware\MiddlewareExample;
use Workbench\App\ActionMiddleware\MiddlewareExampleOne;
use Workbench\App\ActionMiddleware\MiddlewareExampleTwo;

class ExampleActionWithMiddleware extends Action
{
    public function __construct(public array $value)
    {
    }

    public function handle(): int
    {
        MiddlewareExample::$history[] = [static::class, $this];

        return 1234;
    }

    public function middleware(): array
    {
        return [
            MiddlewareExampleOne::class,
            MiddlewareExampleTwo::class,
        ];
    }
}
