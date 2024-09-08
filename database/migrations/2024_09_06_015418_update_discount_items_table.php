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
        Schema::create('discount_items', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('supermarket');
            $table->string('timeslot');
            $table->string('item');
            $table->decimal('original_price', 8, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discounted_price', 8, 2);
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('sold_out')->default(false);
            $table->timestamps();
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