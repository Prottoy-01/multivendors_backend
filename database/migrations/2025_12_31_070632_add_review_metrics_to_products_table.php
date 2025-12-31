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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->default(0.00)->after('stock');
            $table->integer('total_reviews')->default(0)->after('average_rating');
            $table->integer('view_count')->default(0)->after('total_reviews');
            $table->integer('order_count')->default(0)->after('view_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['average_rating', 'total_reviews', 'view_count', 'order_count']);
        });
    }
};
