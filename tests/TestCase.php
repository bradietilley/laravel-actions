<?php

namespace Tests;

use BradieTilley\Actions\ActionServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

abstract class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ActionServiceProvider::class,
        ];
    }
}
