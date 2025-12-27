<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_offer')->default(false)->after('price');

            $table->enum('discount_type', ['percentage', 'fixed'])
                  ->nullable()
                  ->after('has_offer');

            $table->decimal('discount_value', 10, 2)
                  ->nullable()
                  ->after('discount_type');

            $table->timestamp('offer_start')->nullable()->after('discount_value');
            $table->timestamp('offer_end')->nullable()->after('offer_start');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'has_offer',
                'discount_type',
                'discount_value',
                'offer_start',
                'offer_end',
            ]);
        });
    }
};
