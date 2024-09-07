<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiscountItem;

class DiscountItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DiscountItem::create([
            'date' => '2023-04-15',
            'supermarket' => 'SuperMart',
            'item' => 'Apples',
            'original_price' => 2.99,
            'discounted_price' => 1.99,
            'timeslot' => '5:00 - 5:30',
            'photo' => '',
            'discount_percentage' => 33.44,
            'sold_out' => false,
            'notes' => null,

        ]);

        // Add more items as needed
    }
}
