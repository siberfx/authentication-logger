<?php

namespace Siberfx\AuthenticationLogger\Tests;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Auth\User as PlainUser;
use PHPUnit\Framework\Attributes\Test;
use Siberfx\AuthenticationLogger\Models\AuthLogger;
use Siberfx\AuthenticationLogger\Notifications\FailedLogin;
use Siberfx\AuthenticationLogger\Notifications\NewDevice;

class AuthenticationLoggerTest extends TestCase
{
    #[Test]
    public function it_logs_a_successful_login(): void
    {
        $user = $this->createUser();

        event(new Login('web', $user, false));

        $log = $user->authentications()->sole();

        $this->assertTrue($log->login_successful);
        $this->assertSame('127.0.0.1', $log->ip_address);
        $this->assertNotNull($log->login_at);
        $this->assertTrue($user->latestAuthentication->is($log));
    }

    #[Test]
    public function it_logs_a_failed_login_and_notifies_the_user(): void
    {
        Notification::fake();
        $user = $this->createUser();

        event(new Failed('web', $user, ['email' => $user->email]));

        $this->assertFalse($user->authentications()->sole()->login_successful);
        Notification::assertSentTo($user, FailedLogin::class);
    }

    #[Test]
    public function it_ignores_failed_logins_for_unknown_users(): void
    {
        event(new Failed('web', null, ['email' => 'nobody@example.com']));

        $this->assertSame(0, AuthLogger::count());
    }

    #[Test]
    public function it_notifies_about_logins_from_a_new_device(): void
    {
        Notification::fake();
        config()->set('auth-logger.notifications.new-device.enabled', true);
        $user = $this->createUser(['created_at' => now()->subDay()]);

        event(new Login('web', $user, false));
        Notification::assertSentToTimes($user, NewDevice::class, 1);

        event(new Login('web', $user, false));
        Notification::assertSentToTimes($user, NewDevice::class, 1);
    }

    #[Test]
    public function it_does_not_notify_newly_registered_users(): void
    {
        Notification::fake();
        config()->set('auth-logger.notifications.new-device.enabled', true);
        $user = $this->createUser();

        event(new Login('web', $user, false));

        Notification::assertNothingSent();
    }

    #[Test]
    public function it_records_the_logout_time(): void
    {
        $user = $this->createUser();

        event(new Login('web', $user, false));
        event(new Logout('web', $user));

        $this->assertNotNull($user->authentications()->sole()->logout_at);
    }

    #[Test]
    public function it_clears_sessions_on_other_devices(): void
    {
        $user = $this->createUser();
        $other = AuthLogger::factory()->for($user, 'authenticatable')->create(['login_at' => now()->subHour()]);

        event(new Login('web', $user, false));
        event(new OtherDeviceLogout('web', $user));

        $this->assertTrue($other->fresh()->cleared_by_user);
        $this->assertNotNull($other->fresh()->logout_at);
        $this->assertFalse($user->authentications()->first()->cleared_by_user);
    }

    #[Test]
    public function it_exposes_login_history_helpers(): void
    {
        $user = $this->createUser();
        $factory = AuthLogger::factory()->for($user, 'authenticatable');

        $factory->create(['ip_address' => '10.0.0.1', 'login_at' => now()->subDays(2)]);
        $factory->create(['ip_address' => '10.0.0.2', 'login_at' => now()->subDay()]);
        $factory->failed()->create(['ip_address' => '10.0.0.3', 'login_at' => now()]);

        $this->assertSame('10.0.0.3', $user->lastLoginIp());
        $this->assertSame('10.0.0.2', $user->lastSuccessfulLoginIp());
        $this->assertSame('10.0.0.1', $user->previousLoginIp());
        $this->assertTrue($user->lastSuccessfulLoginAt()->isYesterday());
    }

    #[Test]
    public function it_prunes_logs_older_than_the_configured_days(): void
    {
        $user = $this->createUser();
        $factory = AuthLogger::factory()->for($user, 'authenticatable');
        $old = $factory->create(['login_at' => now()->subDays(61)]);
        $recent = $factory->create(['login_at' => now()->subDays(59)]);

        $this->artisan('model:prune', ['--model' => AuthLogger::class])->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }

    #[Test]
    public function it_renders_the_notification_mails(): void
    {
        $user = $this->createUser();
        $log = AuthLogger::factory()->for($user, 'authenticatable')->create();

        foreach ([new NewDevice($log), new FailedLogin($log)] as $notification) {
            $html = (string) $notification->toMail($user)->render();

            $this->assertStringContainsString($user->email, $html);
            $this->assertStringContainsString($log->ip_address, $html);
        }
    }

    #[Test]
    public function it_only_closes_the_open_session_on_logout(): void
    {
        $user = $this->createUser();

        event(new Login('web', $user, false));
        event(new Logout('web', $user));
        $firstLogout = $user->authentications()->sole()->logout_at;

        $this->travel(5)->minutes();
        event(new Login('web', $user, false));
        event(new Logout('web', $user));

        $this->assertSame(2, $user->authentications()->count());
        $this->assertEquals($firstLogout, $user->authentications()->reorder('login_at')->first()->logout_at);
    }

    #[Test]
    public function it_ignores_models_without_the_trait(): void
    {
        $plain = new PlainUser;
        $plain->id = 1;

        event(new Login('web', $plain, false));
        event(new Failed('web', $plain, []));

        $this->assertSame(0, AuthLogger::count());
    }

    #[Test]
    public function it_skips_the_failed_login_notification_when_disabled(): void
    {
        Notification::fake();
        config()->set('auth-logger.notifications.failed-login.enabled', false);
        $user = $this->createUser();

        event(new Failed('web', $user, []));

        $this->assertSame(1, $user->authentications()->failed()->count());
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_leaves_the_location_empty_when_geoip_is_unavailable(): void
    {
        config()->set('auth-logger.notifications.failed-login.location', true);
        $user = $this->createUser();

        event(new Failed('web', $user, []));

        $this->assertNull($user->authentications()->sole()->location);
    }

    #[Test]
    public function it_uses_the_configured_model_and_notification_template(): void
    {
        Notification::fake();
        config()->set('auth-logger.model', CustomAuthLogger::class);
        config()->set('auth-logger.notifications.failed-login.template', CustomFailedLogin::class);
        $user = $this->createUser();

        event(new Failed('web', $user, []));

        $this->assertInstanceOf(CustomAuthLogger::class, $user->authentications()->sole());
        Notification::assertSentTo($user, CustomFailedLogin::class);
    }
}

class CustomAuthLogger extends AuthLogger {}

class CustomFailedLogin extends FailedLogin {}
