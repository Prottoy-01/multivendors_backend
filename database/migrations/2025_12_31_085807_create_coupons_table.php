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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed', 'free_shipping']);
            $table->decimal('value', 10, 2); // Percentage or fixed amount
            $table->decimal('min_purchase', 10, 2)->nullable(); // Minimum purchase requirement
            $table->decimal('max_discount', 10, 2)->nullable(); // Maximum discount cap
            $table->integer('usage_limit')->nullable(); // Total usage limit
            $table->integer('usage_count')->default(0);
            $table->integer('per_user_limit')->default(1); // Per user usage limit
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users'); // Admin who created
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('code');
            $table->index(['is_active', 'valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
