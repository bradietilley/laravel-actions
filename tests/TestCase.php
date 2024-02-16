<?php

namespace Tests;

use BradieTilley\Actionables\Laravel\ActionServiceProvider;
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
