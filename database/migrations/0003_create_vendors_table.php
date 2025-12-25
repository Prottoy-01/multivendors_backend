<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->unsignedBigInteger('user_id'); // FK to users
            $table->string('shop_name', 150);
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->decimal('commission_percentage', 5, 2)->default(10.00);
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
