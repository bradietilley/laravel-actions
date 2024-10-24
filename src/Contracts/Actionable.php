<?php

namespace BradieTilley\Actions\Contracts;

/**
 * This interface provides no handle method to allow you to customise
 * the signature of the handle method for when leveraging Laravel's
 * dependency injection.
 *
 * @method mixed handle()
 */
interface Actionable
{
    /**
     * Middleware to pipe the actionable through before starting.
     *
     * @return array<int, class-string|object>
     */
    public function middleware(): array;
}
