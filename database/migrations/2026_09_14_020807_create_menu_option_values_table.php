<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_group_id')->constrained('menu_option_groups')->cascadeOnDelete();
            $table->string('label');
            $table->decimal('extra_price', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_option_values');
    }
};