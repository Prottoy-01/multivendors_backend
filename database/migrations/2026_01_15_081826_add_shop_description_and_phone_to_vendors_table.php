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
        Schema::table('vendors', function (Blueprint $table) {
            // Add shop_description column if it doesn't exist
            if (!Schema::hasColumn('vendors', 'shop_description')) {
                $table->text('shop_description')->nullable()->after('shop_name');
            }
            
            // Add phone column if it doesn't exist
            if (!Schema::hasColumn('vendors', 'phone')) {
                $table->string('phone', 20)->nullable()->after('shop_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('vendors', 'shop_description')) {
                $table->dropColumn('shop_description');
            }
            
            if (Schema::hasColumn('vendors', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};