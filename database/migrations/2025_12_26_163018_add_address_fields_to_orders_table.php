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
        $table->string('recipient_name');
        $table->string('phone');
        $table->text('address_line');
        $table->string('city');
        $table->string('state')->nullable();
        $table->string('postal_code')->nullable();
        $table->string('country')->default('Bangladesh');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn([
            'recipient_name',
            'phone',
            'address_line',
            'city',
            'state',
            'postal_code',
            'country'
        ]);
    });
    }
};
