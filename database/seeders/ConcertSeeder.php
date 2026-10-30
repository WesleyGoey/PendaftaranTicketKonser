<?php

namespace Database\Seeders;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ConcertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Concert::create([
            'title' => 'Bruno Mars Live in Surabaya',
            'artist' => 'Bruno Mars',
            'concert_date' => Carbon::parse('2027-02-09 18:00:00'),
            'venue' => 'Universitas Ciputra Surabaya, Indonesia',
            'total_tickets' => 100,
            'available_tickets' => 100,
            'price' => 1500000,
            'description' => 'A spectacular night of Bruno Mars hits, soulful vocals, and unforgettable live performances.',
            'image_url' => 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800',
        ]);

    }
}
