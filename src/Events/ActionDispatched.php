<?php

namespace BradieTilley\Actions\Events;

use BradieTilley\Actions\Contracts\Action;
use SebastianBergmann\Timer\Duration;

class ActionDispatched
{
    public function __construct(
        public readonly Action $action,
        public readonly mixed $value,
        public readonly Duration $duration,
    ) {
    }
}
