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
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_change'); // +10 or -5
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->enum('reason', [
                'sale', 'restock', 'return', 'adjustment', 'damaged', 'lost'
            ]);
            $table->foreignId('user_id')->nullable()->constrained(); // Who made the change
            $table->foreignId('order_id')->nullable()->constrained(); // Related order if applicable
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('product_id');
            $table->index(['product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
