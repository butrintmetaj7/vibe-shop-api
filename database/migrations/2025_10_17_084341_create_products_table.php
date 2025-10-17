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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->nullable();
            $table->string('title', 255);
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('category', 100);
            $table->string('image', 500)->nullable();
            $table->decimal('rating_rate', 3, 2)->nullable();
            $table->integer('rating_count')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('external_id');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
