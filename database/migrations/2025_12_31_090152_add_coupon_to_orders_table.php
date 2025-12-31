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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->constrained()->after('discount_total');
            $table->decimal('coupon_discount', 10, 2)->default(0.00)->after('coupon_id');
            $table->decimal('shipping_cost', 10, 2)->default(0.00)->after('coupon_discount');
            $table->decimal('tax_amount', 10, 2)->default(0.00)->after('shipping_cost');
            $table->decimal('grand_total', 10, 2)->after('tax_amount'); // total_amount - coupon + shipping + tax
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_id', 'coupon_discount', 'shipping_cost', 'tax_amount', 'grand_total']);
        });
    }
};
