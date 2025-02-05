<?php

namespace BradieTilley\Actions;

use Stringable;

class Duration implements Stringable
{
    protected float $seconds;

    protected function __construct(public readonly int $nanoseconds)
    {
        $this->seconds = $this->nanoseconds / 1_000_000_000;
        ;
    }

    public function asNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    public function asMicroseconds(): float
    {
        return $this->nanoseconds / 1_000;
    }

    public function asMilliseconds(): float
    {
        return $this->nanoseconds / 1_000_000;
    }

    public function asSeconds(): float
    {
        return $this->nanoseconds / 1_000_000_000;
    }

    public function asMinutes(): float
    {
        return $this->asSeconds() / 60;
    }

    public function asHours(): float
    {
        return $this->asMinutes() / 60;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $hours = floor($this->asHours());
        $minutes = floor($this->asMinutes());
        $seconds = floor($this->asSeconds());
        $milliseconds = $this->asMilliseconds();

        if (floor($milliseconds) <= 0) {
            return sprintf('%dµs', $this->nanoseconds);
        }

        if ($seconds <= 0) {
            return sprintf('%02dms', $this->asMilliseconds());
        }

        $format = '';

        $format .= $hours > 0 ? sprintf('%02d', $hours).':' : '';
        $format .= sprintf('%02d', $minutes).':';
        $format .= sprintf('%02d', $seconds);

        return $format;
    }

    public static function make(int $nanoseconds): self
    {
        return new self($nanoseconds);
    }

    /**
     * Get the current timestamp in nanoseconds
     */
    public static function start(): int
    {
        return (int) hrtime(true);
    }

    /**
     * Compare the given start timestamp with now to form a Duration
     */
    public static function stop(int $start): static
    {
        return new static(static::start() - $start);
    }
}
