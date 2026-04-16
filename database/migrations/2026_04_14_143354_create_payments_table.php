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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('external_id')->unique(); // ID transaksi dari Midtrans
            $table->string('payment_method'); // Contoh: gopay, bank_transfer, credit_card
            $table->decimal('amount', 15, 2); // Presisi tinggi untuk nilai uang
            $table->string('status'); // pending, settlement, expire, deny
            $table->json('raw_response')->nullable(); // Menyimpan seluruh data JSON dari Midtrans untuk audit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
