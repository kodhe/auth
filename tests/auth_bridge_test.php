<?php

declare(strict_types=1);

/**
 * Test suite for the PsrAuthEventDispatcher adapter (kodhe/auth).
 * The psr/event-dispatcher interfaces are stubbed when not installed.
 */

namespace Psr\EventDispatcher {
    if (!interface_exists(EventDispatcherInterface::class, false)) {
        interface EventDispatcherInterface
        {
            public function dispatch(object $event): object;
        }
    }
}

namespace Kodhe\Framework\Auth\Contracts {
    if (!interface_exists(AuthEventDispatcherInterface::class, false)) {
        interface AuthEventDispatcherInterface
        {
            public function dispatch(string $event, array $payload = []): void;
        }
    }
}

namespace Kodhe\Framework\Auth\Bridge\Tests {

    use Kodhe\Framework\Auth\Bridge\AuthEvent;
    use Kodhe\Framework\Auth\Bridge\PsrAuthEventDispatcher;

    require_once __DIR__ . '/../src/Bridge/AuthEvent.php';
    require_once __DIR__ . '/../src/Bridge/PsrAuthEventDispatcher.php';

    class RecordingDispatcher implements \Psr\EventDispatcher\EventDispatcherInterface
    {
        public array $events = [];
        public function dispatch(object $event): object
        {
            $this->events[] = $event;
            return $event;
        }
    }

    $passed = 0;
    $failed = 0;

    function check(string $label, bool $condition): void
    {
        global $passed, $failed;
        if ($condition) { $passed++; echo "ok   - $label\n"; }
        else { $failed++; echo "FAIL - $label\n"; }
    }

    // forwards under documented constant names, wrapped as AuthEvent
    $rec = new RecordingDispatcher();
    $bridge = new PsrAuthEventDispatcher($rec);
    $bridge->dispatch('auth.failed', ['identity' => 'bob']);
    $bridge->dispatch('auth.login', []);

    check(
        'emits AuthEvent with original auth.* name and payload',
        count($rec->events) === 2
            && $rec->events[0] instanceof AuthEvent
            && $rec->events[0]->name() === 'auth.failed'
            && $rec->events[0]->get('identity') === 'bob'
            && $rec->events[1]->name() === 'auth.login'
    );
    check('get(null) returns full payload; default for missing key', $rec->events[0]->get() === ['identity' => 'bob'] && $rec->events[1]->get('x', 'd') === 'd');

    // satisfies the auth contract (typed against the real interface file when autoloaded)
    check('implements AuthEventDispatcherInterface', $bridge instanceof \Kodhe\Framework\Auth\Contracts\AuthEventDispatcherInterface);

    echo "\n$passed passed, $failed failed\n";
    exit($failed === 0 ? 0 : 1);
}
