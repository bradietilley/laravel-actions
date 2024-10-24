<?php

namespace Workbench\App\Actions;

use BradieTilley\Actions\Action;

class ExampleActionB extends Action
{
    public static array $ran = [];

    public function __construct(public array|int $value)
    {
    }

    public function handle(): array|int
    {
        static::$ran[] = $this->value;

        return $this->value;
    }
}
