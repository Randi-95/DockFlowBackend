<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('waiting', 'confirmed', 'processing', 'on_delivery', 'completed', 'cancelled') NOT NULL");
    }

   
    public function down(): void
    {
    
        DB::statement("UPDATE bookings SET status = 'processing' WHERE status = 'on_delivery'");
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('waiting', 'confirmed', 'processing', 'completed', 'cancelled') NOT NULL");
    }
};
