<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('vessel_id')->constrained('vessels');
            $table->string('dock_location')->nullable();
            $table->dateTime('estimated_delivery_date');
            $table->decimal('total_estimated_price', 15, 2);
            $table->enum('status', ['waiting', 'confirmed', 'processing', 'completed', 'cancelled']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
