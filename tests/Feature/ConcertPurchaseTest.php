<?php

namespace Tests\Feature;

use App\Mail\RegistrationEmail;
use App\Models\Concert;
use App\Models\TicketPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConcertPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_user_can_book_tickets_for_their_own_account(): void
    {
        Mail::fake();
        $buyer = User::factory()->create(['name' => 'Concert Guest', 'email' => 'guest@example.com']);

        $concert = Concert::create([
            'title' => 'Konser Uji',
            'artist' => 'Artis Uji',
            'concert_date' => now()->addWeek(),
            'venue' => 'Gedung Uji',
            'total_tickets' => 100,
            'available_tickets' => 10,
            'price' => 150000,
        ]);

        // name/email dari request harus diabaikan.
        $response = $this->actingAs($buyer)->post(route('concerts.store'), [
            'name' => 'Someone Else',
            'email' => 'victim@example.com',
            'phone' => '+62 812-3456-7890',
            'concert_id' => $concert->id,
            'quantity' => 2,
            'seat_type' => 'regular',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Please check your email to find your booking code.');
        $user = User::where('email', 'guest@example.com')->firstOrFail();
        $this->assertDatabaseHas('ticket_purchases', [
            'user_id' => $user->id,
            'concert_id' => $concert->id,
            'quantity' => 2,
            'total_price' => 2000000,
            'status' => 'pending',
            'seat_type' => 'regular',
            'phone' => '+62 812-3456-7890',
        ]);
        $this->assertDatabaseMissing('users', ['email' => 'victim@example.com']);
        $this->assertDatabaseHas('users', ['id' => $buyer->id, 'name' => 'Concert Guest']);
        $purchase = $user->ticketPurchases()->firstOrFail();
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $purchase->confirmation_code);
        $this->assertDatabaseHas('concerts', [
            'id' => $concert->id,
            'available_tickets' => 8,
        ]);
        Mail::assertSent(RegistrationEmail::class, function (RegistrationEmail $mail): bool {
            return $mail->hasTo('guest@example.com')
                && $mail->purchase->seat_type === 'regular'
                && $mail->purchase->quantity === 2
                && str_contains($mail->render(), $mail->purchase->confirmation_code)
                && str_contains($mail->render(), 'Your booking is confirmed, Concert Guest!')
                && str_contains($mail->render(), '<strong>Date:</strong>')
                && str_contains($mail->render(), '18.00 - 22.00')
                && str_contains($mail->render(), 'Universitas Ciputra Surabaya, Indonesia')
                && str_contains($mail->render(), '+62 812-3456-7890');
        });
    }

    public function test_homepage_creates_the_fixed_concert_when_database_is_empty(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Bruno Mars Live in Surabaya');
        $response->assertSee('Regular');
        $response->assertSee('Premium');
        $response->assertSee('VIP');
        $response->assertSee('Diamond');
        $this->assertDatabaseHas('concerts', [
            'artist' => 'Bruno Mars',
            'available_tickets' => 100,
            'venue' => 'Universitas Ciputra Surabaya, Indonesia',
        ]);
    }

    public function test_guest_is_sent_to_login_and_nothing_is_booked(): void
    {
        $concert = $this->makeConcert();

        $this->post(route('concerts.store'), [
            'phone' => '+62 812-3456-7890',
            'concert_id' => $concert->id,
            'quantity' => 1,
            'seat_type' => 'regular',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('ticket_purchases', 0);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_guest_can_browse_but_is_asked_to_log_in(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Log in to book')
            ->assertDontSee('Confirm booking')
            ->assertDontSee('My Tickets');

        $this->get(route('tickets.index'))->assertRedirect(route('login'));
    }

    public function test_signed_in_user_sees_prefilled_buyer_details(): void
    {
        $user = User::factory()->create(['phone' => '+62 811-1111-1111']);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee('value="+62 811-1111-1111"', false)
            ->assertSee('Confirm booking')
            ->assertSee('My Tickets');
    }

    public function test_my_tickets_only_shows_the_users_own_bookings(): void
    {
        $concert = $this->makeConcert();
        $me = User::factory()->create();
        $other = User::factory()->create();

        foreach ([[$me, 'MINE0001'], [$other, 'THEIRS01']] as [$user, $code]) {
            TicketPurchase::create([
                'confirmation_code' => $code,
                'phone' => '+62 812-3456-7890',
                'user_id' => $user->id,
                'concert_id' => $concert->id,
                'quantity' => 2,
                'seat_type' => 'vip',
                'total_price' => 6000000,
                'status' => 'pending',
            ]);
        }

        $this->actingAs($me)->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('MINE0001')
            ->assertSee('Rp 6.000.000')
            ->assertDontSee('THEIRS01');
    }

    public function test_my_tickets_explains_an_empty_history(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('No bookings yet');
    }

    public function test_concert_pages_show_the_cheapest_seat_price(): void
    {
        $concert = $this->makeConcert();

        $this->get(route('concerts.show', $concert))
            ->assertOk()
            ->assertSee('Rp 1.000.000')
            ->assertSee('Diamond')
            ->assertSee('Book tickets');
    }

    public function test_user_cannot_register_more_tickets_than_available(): void
    {
        $concert = Concert::create([
            'title' => 'Konser Habis',
            'artist' => 'Artis Uji',
            'concert_date' => now()->addWeek(),
            'venue' => 'Gedung Uji',
            'total_tickets' => 1,
            'available_tickets' => 1,
            'price' => 100000,
        ]);

        $response = $this->actingAs(User::factory()->create())->from(route('home'))->post(route('concerts.store'), [
            'phone' => '+62 812-3456-7890',
            'concert_id' => $concert->id,
            'quantity' => 2,
            'seat_type' => 'regular',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'There are not enough tickets available.');
        $this->assertDatabaseCount('ticket_purchases', 0);
        $this->assertDatabaseHas('concerts', [
            'id' => $concert->id,
            'available_tickets' => 1,
        ]);
    }

    public function test_booking_requires_customer_details(): void
    {
        $response = $this->actingAs(User::factory()->create())->post(route('concerts.store'), [
            'concert_id' => 1,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors(['phone', 'seat_type']);
        $this->assertDatabaseCount('ticket_purchases', 0);
    }

    private function makeConcert(): Concert
    {
        return Concert::create([
            'title' => 'Konser Uji',
            'artist' => 'Artis Uji',
            'concert_date' => now()->addWeek(),
            'venue' => 'Gedung Uji',
            'total_tickets' => 100,
            'available_tickets' => 10,
            'price' => 150000,
        ]);
    }
}
