<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Concert extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'concert_date',
        'venue',
        'total_tickets',
        'available_tickets',
        'price',
        'description',
        'image_url'
    ];

    public function ticketPurchases(): HasMany
    {
        return $this->hasMany(TicketPurchase::class);
    }
}
