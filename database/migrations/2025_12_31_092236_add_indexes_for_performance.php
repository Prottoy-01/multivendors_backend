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
        //
        Schema::table('products', function (Blueprint $table) {
            $table->index('vendor_id');
            $table->index('category_id');
            $table->index('price');
            $table->index('created_at');
            $table->index(['vendor_id', 'category_id']);
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('vendor_id');
            $table->index('status');
            $table->index('created_at');
            $table->index(['vendor_id', 'status']);
            $table->index(['user_id', 'status']);
        });
        
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('cart_id');
            $table->index('product_id');
        });
        
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['price']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['vendor_id', 'category_id']);
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['vendor_id', 'status']);
            $table->dropIndex(['user_id', 'status']);
        });
        
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['cart_id']);
            $table->dropIndex(['product_id']);
        });
        
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['product_id']);
        });
    }
};
