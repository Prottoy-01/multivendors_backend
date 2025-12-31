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
            $table->string('tracking_number')->nullable()->after('status');
            $table->string('carrier')->nullable()->after('tracking_number'); // Shipping company
            $table->timestamp('shipped_at')->nullable()->after('carrier');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->text('cancellation_reason')->nullable()->after('delivered_at');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->after('cancellation_reason');
            $table->boolean('is_returnable')->default(true)->after('cancelled_by');
            $table->integer('return_window_days')->default(7)->after('is_returnable');
            
            $table->index('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_number', 'carrier', 'shipped_at', 'delivered_at',
                'cancellation_reason', 'cancelled_by', 'is_returnable', 'return_window_days'
            ]);
        });
    }
};
