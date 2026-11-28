<?php

namespace Tests\Feature;

use App\Mail\RegistrationEmail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\FakesSupabase;
use Tests\TestCase;

class ConcertPurchaseTest extends TestCase
{
    use FakesSupabase;

    /**
     * @return array<string, mixed>
     */
    private function booking(array $overrides = []): array
    {
        return ['phone' => '+62 812-3456-7890', 'concert_id' => 1, 'quantity' => 2, 'seat_type' => 'vip', ...$overrides];
    }

    public function test_signed_in_user_books_through_the_database_function_with_their_own_token(): void
    {
        Mail::fake();
        $this->fakeSupabase();

        // name, email, dan harga dari request harus diabaikan.
        $this->signedIn()->from(route('home'))
            ->post(route('concerts.store'), $this->booking(['email' => 'victim@example.com', 'total_price' => 1]))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Please check your email to find your booking code.');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/rest/v1/rpc/book_tickets')
            && $request->hasHeader('Authorization', 'Bearer user-access-token')
            && $request->data() === [
                'p_concert_id' => 1, 'p_quantity' => 2, 'p_seat_type' => 'vip', 'p_phone' => '+62 812-3456-7890',
            ]);

        Mail::assertSent(RegistrationEmail::class, function (RegistrationEmail $mail): bool {
            $html = $mail->render();

            return $mail->hasTo('buyer@example.com')
                && str_contains($html, 'Your booking is confirmed, Test Buyer!')
                && str_contains($html, 'MINE0001')
                && str_contains($html, '9 February 2027')
                && str_contains($html, 'View My Tickets')
                && ! str_contains($html, 'Total paid')
                && str_contains($html, 'Universitas Ciputra Surabaya, Indonesia')
                && str_contains($html, '+62 812-3456-7890')
                && str_contains($html, 'Rp 6.000.000');
        });
    }

    public function test_user_is_told_when_there_are_not_enough_tickets(): void
    {
        Mail::fake();
        $this->fakeSupabase(['*/rest/v1/rpc/book_tickets' => Http::response(['code' => 'P0001', 'message' => 'not_enough_tickets'], 400)]);

        $this->signedIn()->from(route('home'))->post(route('concerts.store'), $this->booking())
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'There are not enough tickets available.');

        Mail::assertNothingSent();
    }

    public function test_guest_is_sent_to_login_and_nothing_is_booked(): void
    {
        Http::fake();

        $this->post(route('concerts.store'), $this->booking())->assertRedirect(route('login'));

        Http::assertNothingSent();
    }

    public function test_booking_requires_phone_and_a_known_seat(): void
    {
        $this->fakeSupabase();

        $this->signedIn()->post(route('concerts.store'), ['concert_id' => 1, 'quantity' => 1, 'seat_type' => 'sofa'])
            ->assertSessionHasErrors(['phone', 'seat_type']);

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'book_tickets'));
    }

    public function test_guest_can_browse_but_is_asked_to_log_in(): void
    {
        $this->fakeSupabase();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Bruno Mars Live in Surabaya')
            ->assertSee('Log in to book')
            ->assertDontSee('Confirm booking')
            ->assertDontSee('Sign up')
            ->assertDontSee(route('tickets.index'), false);

        $this->get(route('tickets.index'))->assertRedirect(route('login'));
    }

    public function test_signed_in_user_sees_prefilled_buyer_details(): void
    {
        $this->fakeSupabase();

        $this->signedIn()->get(route('home'))
            ->assertOk()
            ->assertSee('buyer@example.com')
            ->assertSee('value="+62 812-3456-7890"', false)
            ->assertSee('Confirm booking')
            ->assertSee('My Tickets');
    }

    public function test_homepage_still_loads_when_supabase_is_not_ready(): void
    {
        Http::fake(['*' => Http::response(['message' => 'relation does not exist'], 404)]);

        $this->get(route('home'))->assertOk()->assertSee('Tickets are not available right now.');
    }

    public function test_my_tickets_asks_supabase_with_the_users_token(): void
    {
        $this->fakeSupabase();

        $this->signedIn()->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('MINE0001')
            ->assertSee('Rp 6.000.000')
            ->assertSee('28 Nov 2026, 09.52 WIB');

        // Pembatasan "hanya pesanan sendiri" dilakukan RLS, jadi token user wajib dipakai.
        Http::assertSent(fn ($request) => str_contains($request->url(), '/rest/v1/ticket_purchases')
            && $request->hasHeader('Authorization', 'Bearer user-access-token'));
    }

    public function test_my_tickets_explains_an_empty_history(): void
    {
        $this->fakeSupabase(['*/rest/v1/ticket_purchases*' => Http::response([])]);

        $this->signedIn()->get(route('tickets.index'))->assertOk()->assertSee('No bookings yet');
    }

    public function test_concert_page_and_missing_concert(): void
    {
        $this->fakeSupabase();

        $this->get(route('concerts.show', 1))
            ->assertOk()
            ->assertSee('Rp 1.000.000')
            ->assertSee('Diamond')
            ->assertSee('Back to home');
    }

    public function test_unknown_concert_is_not_found(): void
    {
        $this->fakeSupabase(['*/rest/v1/concerts*' => Http::response([])]);

        $this->get(route('concerts.show', 99))->assertNotFound();
    }

    public function test_old_concert_list_address_redirects_home(): void
    {
        $this->get('/concerts')->assertRedirect('/');
    }

    public function test_data_pages_have_live_regions_for_realtime(): void
    {
        $this->fakeSupabase();

        $this->get(route('home'))->assertSee('data-live="hero-ticket"', false)->assertSee('data-live="booking-title"', false);
        $this->get(route('concerts.show', 1))->assertSee('data-live="concert"', false);
        $this->signedIn()->get(route('tickets.index'))->assertSee('data-live="tickets"', false);
    }

    /**
     * Harga yang ditagih ditentukan SQL; harus sama dengan yang ditampilkan.
     */
    public function test_seat_prices_in_the_database_match_the_prices_on_the_page(): void
    {
        $sql = file_get_contents(base_path('supabase/schema.sql'));

        foreach (config('tickets.seats') as $type => $seat) {
            $this->assertStringContainsString("when '{$type}' then {$seat['price']}", $sql);
        }
    }
}
