<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discount_items', function (Blueprint $table) {
            $table->date('date');
            $table->string('supermarket');
            $table->string('timeslot');
            $table->string('item');
            $table->string('photo')->nullable();
            $table->decimal('original_price', 8, 2);
            $table->string('discount_percentage');
            $table->decimal('discounted_price', 8, 2);
            $table->boolean('sold_out')->default(false);
            $table->text('notes')->nullable();
            // Removed $table->timestamps() as it's likely already in the table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_items', function (Blueprint $table) {
            $table->dropColumn([
                'date', 'supermarket', 'timeslot', 'item', 'photo',
                'original_price', 'discount_percentage', 'discounted_price',
                'sold_out', 'notes'
            ]);
        });
    }
};