<?php

namespace Siberfx\AuthenticationLogger\Tests;

use Illuminate\Auth\Events\Login;
use PHPUnit\Framework\Attributes\Test;

class ListenerToggleTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('auth-logger.listeners.login', false);
    }

    #[Test]
    public function it_does_not_register_disabled_listeners(): void
    {
        $user = $this->createUser();

        event(new Login('web', $user, false));

        $this->assertSame(0, $user->authentications()->count());
    }
}
