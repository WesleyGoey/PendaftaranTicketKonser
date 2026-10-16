<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Concert;
use Carbon\Carbon;

class ConcertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Concert::create([
            'title' => 'Rock Night Festival',
            'artist' => 'The Electric Kings',
            'concert_date' => Carbon::parse('2026-12-15 20:00:00'),
            'venue' => 'Grand Arena',
            'total_tickets' => 100,
            'available_tickets' => 100,
            'price' => 75.50,
            'description' => 'Experience an electrifying night of rock music featuring The Electric Kings and special guests. This outdoor festival will showcase the best in classic and modern rock.',
            'image_url' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=800'
        ]);

        Concert::create([
            'title' => 'Jazz Under the Stars',
            'artist' => 'Smooth Horizons Quartet',
            'concert_date' => Carbon::parse('2026-11-30 19:30:00'),
            'venue' => 'City Garden Amphitheater',
            'total_tickets' => 50,
            'available_tickets' => 50,
            'price' => 45.00,
            'description' => 'An intimate evening of smooth jazz under the open sky. Featuring the Smooth Horizons Quartet with special guest vocalists.',
            'image_url' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=800'
        ]);

        Concert::create([
            'title' => 'Pop Sensations Tour',
            'artist' => 'Luna Starlight',
            'concert_date' => Carbon::parse('2027-01-20 20:00:00'),
            'venue' => 'Metropolitan Stadium',
            'total_tickets' => 200,
            'available_tickets' => 200,
            'price' => 99.99,
            'description' => 'Join international pop sensation Luna Starlight for her highly anticipated world tour stop. Expect spectacular choreography, stunning visuals, and all your favorite hits.',
            'image_url' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=800'
        ]);

        Concert::create([
            'title' => 'Classical Masters',
            'artist' => 'Royal Philharmonic Orchestra',
            'concert_date' => Carbon::parse('2026-12-05 19:00:00'),
            'venue' => 'Symphony Hall',
            'total_tickets' => 75,
            'available_tickets' => 75,
            'price' => 65.00,
            'description' => 'An evening of timeless classical masterpieces performed by the world-renowned Royal Philharmonic Orchestra.',
            'image_url' => 'https://images.unsplash.com/photo-1485298621790-3351a4a77d06?w=800'
        ]);
    }
}
