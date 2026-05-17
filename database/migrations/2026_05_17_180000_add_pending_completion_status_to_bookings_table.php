<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('waiting', 'confirmed', 'processing', 'on_delivery', 'pending_completion', 'completed', 'cancelled') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE bookings SET status = 'on_delivery' WHERE status = 'pending_completion'");
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('waiting', 'confirmed', 'processing', 'on_delivery', 'completed', 'cancelled') NOT NULL");
    }
};
