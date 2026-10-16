<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'concert_id',
        'quantity',
        'total_price',
        'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }
}
