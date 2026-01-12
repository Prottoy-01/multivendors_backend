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
        // Step 1: Add applies_to_all column to coupons table
        if (!Schema::hasColumn('coupons', 'applies_to_all')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->boolean('applies_to_all')->default(true)->after('is_active');
            });
        }
        
        // Step 2: Create coupon_category pivot table
        if (!Schema::hasTable('coupon_category')) {
            Schema::create('coupon_category', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
                $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
                $table->timestamps();
                
                $table->unique(['coupon_id', 'category_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_category');
        
        if (Schema::hasColumn('coupons', 'applies_to_all')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('applies_to_all');
            });
        }
    }
};