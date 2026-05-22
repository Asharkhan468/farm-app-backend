<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('pond_id')->constrained()->onDelete('cascade');
            $table->string('culture_id')->nullable();
            $table->enum('harvest_type', ['partial', 'final']);
            $table->string('species');
            // Stores array of {size, quantity}
            $table->json('size_entries');
            $table->decimal('total_calculated_quantity', 10, 2)->nullable();
            $table->decimal('total_calculated_weight', 10, 3)->nullable();
            $table->decimal('calculated_avg_size', 10, 2)->nullable();
            $table->string('total_feed')->nullable();
            $table->date('date');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
