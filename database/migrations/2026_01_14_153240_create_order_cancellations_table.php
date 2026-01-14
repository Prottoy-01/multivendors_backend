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
        Schema::create('order_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Cancellation info
            $table->string('cancelled_by'); // 'customer' or 'vendor'
            $table->text('cancellation_reason')->nullable();
            $table->string('order_status_at_cancellation'); // Status when cancelled
            
            // Refund info
            $table->decimal('original_amount', 10, 2);
            $table->decimal('refund_amount', 10, 2);
            $table->decimal('refund_percentage', 5, 2); // e.g., 40.00 for 40%
            $table->decimal('vendor_retention', 10, 2); // Amount kept by vendor
            
            $table->enum('refund_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('refund_processed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_cancellations');
    }
};