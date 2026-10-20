<?php

declare(strict_types=1);

namespace Kodhe\Framework\Auth\Bridge;

use Kodhe\Framework\Auth\Contracts\AuthEventDispatcherInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Adapts any PSR-14 dispatcher to kodhe/auth's lightweight
 * AuthEventDispatcherInterface contract.
 *
 * Lives in kodhe/auth (not kodhe/events) so the events package stays a pure,
 * general-purpose dispatcher: auth only depends on the standard
 * psr/event-dispatcher contract, and works with ANY PSR-14 implementation
 * (kodhe/events, Symfony, League, ...).
 *
 *     $auth->setEventDispatcher(new PsrAuthEventDispatcher($psr14Dispatcher));
 *
 * AuthEvents constants already carry the 'auth.' prefix (e.g.
 * AuthEvents::FAILED === 'auth.failed'), so emissions are forwarded under
 * their original names — listeners registered directly on the constant keep
 * working:
 *
 *     $dispatcher->on(AuthEvents::FAILED, fn (AuthEvent $e) => throttle($e->get('identity')));
 *     $dispatcher->on('auth.*', fn (AuthEvent $e) => audit($e->name(), $e->payload()));
 */
class PsrAuthEventDispatcher implements AuthEventDispatcherInterface
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * @param string $event One of the Kodhe\Framework\Auth\AuthEvents constants.
     * @param array  $payload Event-specific data (see AuthEvents docblock).
     */
    public function dispatch(string $event, array $payload = []): void
    {
        // AuthEvents constants are already namespaced ('auth.xxx'); pass the
        // name through unchanged so subscriptions match the documented values.
        $this->dispatcher->dispatch(new AuthEvent($event, $payload));
    }
}
