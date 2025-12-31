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
         Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Color, Size, Material, etc.
            $table->string('display_name');
            $table->enum('type', ['select', 'color', 'text']); // How to display
            $table->boolean('is_filterable')->default(true);
            $table->timestamps();
        });
        
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('product_attributes')->onDelete('cascade');
            $table->string('value'); // Red, Blue, Small, Large, etc.
            $table->string('color_code')->nullable(); // #FF0000 for colors
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attributes');
    }
};
