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
        Schema::create('coupon_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate entries
            $table->unique(['coupon_id', 'category_id']);
            
            // Indexes for faster lookups
            $table->index('coupon_id');
            $table->index('category_id');
        });
        
        // Add column to coupons table to indicate if it applies to all products
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('applies_to_all')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_category');
        
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('applies_to_all');
        });
    }
};