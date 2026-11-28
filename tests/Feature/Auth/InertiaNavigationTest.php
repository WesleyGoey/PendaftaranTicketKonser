<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Inertia\Inertia;
use Tests\Concerns\FakesSupabase;
use Tests\TestCase;

/**
 * Login dan register memakai Inertia, halaman lain Blade. Kalau Inertia menerima
 * jawaban non-Inertia, jawabannya ditampilkan dalam kotak di atas halaman.
 */
class InertiaNavigationTest extends TestCase
{
    use FakesSupabase;

    /**
     * @return array<string, string>
     */
    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) Inertia::getVersion()];
    }

    public function test_an_inertia_visit_to_a_blade_page_becomes_a_full_page_load(): void
    {
        $this->fakeSupabase();
        $this->get(route('login'));

        $this->get(route('home'), $this->inertia())
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('home'));
    }

    public function test_inertia_visits_between_auth_pages_stay_inside_inertia(): void
    {
        $this->get(route('login'));

        $this->get(route('register'), $this->inertia())
            ->assertOk()
            ->assertHeader('X-Inertia', 'true');
    }

    public function test_normal_page_loads_are_not_affected(): void
    {
        $this->fakeSupabase();

        $this->get(route('home'))->assertOk()->assertSee('Book your tickets');
    }

    public function test_too_many_attempts_show_a_message_on_the_form(): void
    {
        $this->fakeSupabase();

        foreach (range(1, 7) as $attempt) {
            $response = $this->from(route('register'))->post(route('register.store'), [], $this->inertia());
        }

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email' => 'Too many attempts. Please wait a minute and try again.']);
    }

    public function test_an_expired_form_goes_back_with_a_message(): void
    {
        $this->app['env'] = 'production';
        $this->app->bind(PreventRequestForgery::class, fn ($app) => new class($app, $app['encrypter']) extends PreventRequestForgery
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => 'a@b.test', 'password' => 'x'], $this->inertia() + ['Sec-Fetch-Site' => 'cross-site'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'This page expired. Please try again.']);
    }
}
