<?php

namespace Workbench\App\Actions;

class ExampleAlternativeActionB extends ExampleActionB
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
