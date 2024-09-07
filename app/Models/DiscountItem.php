<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'supermarket',
        'timeslot',
        'notes',
        'photo',
        'item',
        'original_price',
        'discount_percentage',
        'discounted_price',
    ];

    protected $casts = [
        'date' => 'date',
        'original_price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'sold_out' => 'boolean',
    ];

    // If you want to group discount items by supermarket
    public function scopeSupermarket($query, $supermarket)
    {
        return $query->where('supermarket', $supermarket);
    }

    // If you want to group discount items by date
    public function scopeDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
}
