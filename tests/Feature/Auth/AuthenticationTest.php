<?php

namespace Tests\Feature\Auth;

use App\Auth\SupabaseGuard;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Concerns\FakesSupabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use FakesSupabase;

    private function logIn()
    {
        return $this->post(route('login.store'), ['email' => 'Buyer@Example.com', 'password' => 'secret-password']);
    }

    public function test_login_screen_can_be_rendered()
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_login_checks_supabase_and_keeps_the_tokens_in_the_server_session()
    {
        $this->fakeSupabase();

        $this->logIn()->assertRedirect(route('home'));

        $this->assertSame('user-access-token', session(SupabaseGuard::SESSION_KEY.'.access_token'));
        Http::assertSent(fn ($request) => str_contains($request->url(), 'grant_type=password')
            && $request['email'] === 'buyer@example.com'
            && $request->hasHeader('apikey', 'test-publishable-key'));
    }

    public function test_inertia_login_forces_a_full_page_load_to_the_blade_page()
    {
        $this->fakeSupabase();

        $this->withHeader('X-Inertia', 'true')->post(route('login.store'), [
            'email' => 'buyer@example.com', 'password' => 'secret-password',
        ])->assertStatus(409)->assertHeader('X-Inertia-Location');
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $this->fakeSupabase(['*/auth/v1/token*' => Http::response(['error_code' => 'invalid_credentials'], 400)]);

        $this->logIn()->assertSessionHasErrors('email')->assertSessionMissing(SupabaseGuard::SESSION_KEY);
    }

    public function test_user_is_told_to_confirm_their_email()
    {
        $this->fakeSupabase(['*/auth/v1/token*' => Http::response(['error_code' => 'email_not_confirmed'], 400)]);

        $this->logIn()->assertSessionHasErrors([
            'email' => 'Please confirm your email first. Open the link we sent to your inbox.',
        ]);
    }

    public function test_user_gets_a_clear_message_when_supabase_is_unreachable()
    {
        Http::fake(fn () => throw new ConnectionException('offline'));

        $this->logIn()->assertSessionHasErrors([
            'email' => 'The login service cannot be reached right now. Please try again in a moment.',
        ]);
    }

    public function test_missing_configuration_shows_the_same_message()
    {
        config(['services.supabase.url' => null]);

        $this->logIn()->assertSessionHasErrors('email');
    }

    public function test_a_rejected_token_signs_the_user_out()
    {
        $this->fakeSupabase(['*/auth/v1/user' => Http::response(['error_code' => 'bad_jwt'], 403)]);

        $this->signedIn()->get(route('tickets.index'))
            ->assertRedirect(route('login'))
            ->assertSessionMissing(SupabaseGuard::SESSION_KEY);
    }

    public function test_an_expiring_token_is_refreshed()
    {
        $this->fakeSupabase(['*/auth/v1/token*' => Http::response($this->supabaseSession('fresh-token'))]);

        $this->signedIn(expiresIn: 10)->get(route('tickets.index'))->assertOk();

        $this->assertSame('fresh-token', session(SupabaseGuard::SESSION_KEY.'.access_token'));
        Http::assertSent(fn ($request) => str_contains($request->url(), 'grant_type=refresh_token'));
        Http::assertSent(fn ($request) => str_contains($request->url(), '/rest/v1/ticket_purchases')
            && $request->hasHeader('Authorization', 'Bearer fresh-token'));
    }

    public function test_logout_revokes_only_this_users_supabase_session()
    {
        $this->fakeSupabase();

        $this->signedIn()->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionMissing(SupabaseGuard::SESSION_KEY);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/auth/v1/logout?scope=local')
            && $request->hasHeader('Authorization', 'Bearer user-access-token'));
    }

    public function test_users_are_rate_limited()
    {
        RateLimiter::increment(md5('login'.'buyer@example.com|127.0.0.1'), amount: 5);

        $this->logIn()->assertTooManyRequests();
    }
}
