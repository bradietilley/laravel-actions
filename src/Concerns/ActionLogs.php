<?php

namespace BradieTilley\Actions\Concerns;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Stringable;

trait ActionLogs
{
    protected LoggerInterface|null $logger = null;

    /** @var array<int|string, mixed> */
    protected array $context = [];

    protected function useLogChannel(string|null $channel = null): LoggerInterface
    {
        return $this->logger = Log::channel($channel);
    }

    protected function logger(): LoggerInterface
    {
        return $this->logger ??= Log::channel();
    }

    /**
     * System is unusable.
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Normal but significant events.
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Detailed debug information.
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string   $level
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $context = array_replace($this->context, $context);

        $this->logger()->{$level}($message, $context);
    }
}
