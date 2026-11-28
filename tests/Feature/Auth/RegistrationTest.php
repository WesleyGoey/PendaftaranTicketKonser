<?php

namespace Tests\Feature\Auth;

use App\Auth\SupabaseGuard;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesSupabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use FakesSupabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function input(array $overrides = []): array
    {
        return [
            'name' => 'Test User',
            'email' => 'Test@Example.com',
            'phone' => '+62 812-3456-7890',
            'password' => 'password',
            'password_confirmation' => 'password',
            ...$overrides,
        ];
    }

    public function test_registration_screen_can_be_rendered()
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_new_users_are_registered_in_supabase_and_asked_to_check_email()
    {
        $this->fakeSupabase();

        $this->post(route('register.store'), $this->input())
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Check your email to confirm your account, then log in.')
            ->assertSessionMissing(SupabaseGuard::SESSION_KEY);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/auth/v1/signup?redirect_to=')
            && $request['email'] === 'test@example.com'
            && $request['data'] === ['display_name' => 'Test User', 'phone' => '+62 812-3456-7890']);
    }

    public function test_phone_number_and_password_confirmation_are_required()
    {
        Http::fake();

        $this->post(route('register.store'), $this->input(['phone' => '', 'password_confirmation' => 'different']))
            ->assertSessionHasErrors(['phone', 'password']);

        Http::assertNothingSent();
    }

    public function test_supabase_rejection_is_shown_on_the_form()
    {
        $this->fakeSupabase(['*/auth/v1/signup*' => Http::response(['error_code' => 'weak_password', 'msg' => 'Password is too weak.'], 422)]);

        $this->post(route('register.store'), $this->input())
            ->assertSessionHasErrors(['email' => 'Password is too weak.']);
    }

    public function test_a_simple_password_is_accepted_in_production()
    {
        $this->fakeSupabase();
        $this->app['env'] = 'production';
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->post(route('register.store'), $this->input(['password' => 'admin123', 'password_confirmation' => 'admin123']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));
    }
}
