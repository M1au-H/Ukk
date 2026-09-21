<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('table_id')->constrained('tables')->restrictOnDelete();
            $table->string('session_token', 64)->index();
            $table->string('customer_name');
            $table->enum('payment_status', ['menunggu_pembayaran', 'success', 'failed', 'expired'])
                ->default('menunggu_pembayaran');
            $table->enum('kitchen_status', ['diterima_dapur', 'diproses', 'siap_disajikan', 'selesai'])
                ->default('diterima_dapur');
            $table->decimal('total_price', 12, 2);
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('snap_token')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['table_id', 'kitchen_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};