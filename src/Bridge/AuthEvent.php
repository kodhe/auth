<?php

declare(strict_types=1);

namespace Kodhe\Framework\Auth\Bridge;

/**
 * PSR-14-compatible event object emitted by PsrAuthEventDispatcher.
 *
 * Deliberately self-contained (no dependency on kodhe/events) so the auth
 * guard can emit real PSR-14 events through any dispatcher implementation.
 */
final class AuthEvent
{
    public function __construct(
        private readonly string $name,
        private readonly array $payload = [],
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->payload;
        }

        return $this->payload[$key] ?? $default;
    }
}
