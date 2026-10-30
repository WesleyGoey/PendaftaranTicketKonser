<?php

namespace Tests\Feature;

use App\Mail\RegistrationEmail;
use App\Models\Concert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConcertPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_for_tickets_from_the_homepage(): void
    {
        Mail::fake();

        $concert = Concert::create([
            'title' => 'Konser Uji',
            'artist' => 'Artis Uji',
            'concert_date' => now()->addWeek(),
            'venue' => 'Gedung Uji',
            'total_tickets' => 100,
            'available_tickets' => 10,
            'price' => 150000,
        ]);

        $response = $this->post(route('concerts.store'), [
            'name' => 'Concert Guest',
            'email' => 'guest@example.com',
            'phone' => '+62 812-3456-7890',
            'notes' => 'Please contact me about the booking details.',
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
            'notes' => 'Please contact me about the booking details.',
        ]);
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
                && str_contains($mail->render(), '+62 812-3456-7890')
                && str_contains($mail->render(), 'Please contact me about the booking details.');
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

    public function test_booking_email_uses_the_name_entered_for_an_existing_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'guest@example.com',
        ]);
        $concert = Concert::create([
            'title' => 'Konser Uji',
            'artist' => 'Artis Uji',
            'concert_date' => now()->addWeek(),
            'venue' => 'Gedung Uji',
            'total_tickets' => 100,
            'available_tickets' => 10,
            'price' => 150000,
        ]);

        $this->post(route('concerts.store'), [
            'name' => 'Wes',
            'email' => 'guest@example.com',
            'phone' => '+62 812-3456-7890',
            'concert_id' => $concert->id,
            'quantity' => 1,
            'seat_type' => 'regular',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Wes',
        ]);
        Mail::assertSent(RegistrationEmail::class, function (RegistrationEmail $mail): bool {
            return str_contains($mail->render(), 'Your booking is confirmed, Wes!');
        });
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

        $response = $this->from(route('home'))->post(route('concerts.store'), [
            'name' => 'Concert Guest',
            'email' => 'guest@example.com',
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
        $response = $this->post(route('concerts.store'), [
            'concert_id' => 1,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
        $this->assertDatabaseCount('ticket_purchases', 0);
    }
}
