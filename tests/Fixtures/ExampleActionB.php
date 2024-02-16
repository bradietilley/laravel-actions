<?php

namespace Tests\Fixtures;

use BradieTilley\Actionables\Action;

class ExampleActionB extends Action
{
    public static array $ran = [];

    public function __construct(public array $value)
    {
    }

    public function handle(): array
    {
        static::$ran[] = $this->value;

        return $this->value;
    }
}
