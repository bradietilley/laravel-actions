<?php

namespace BradieTilley\Actions\Events;

use BradieTilley\Actions\Contracts\Actionable;
use BradieTilley\Actions\Duration;

class ActionDispatched
{
    public function __construct(
        public readonly Actionable $action,
        public readonly mixed $value,
        public readonly Duration $duration,
    ) {
    }
}
