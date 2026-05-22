<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ponds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->string('culture_id')->unique();
            $table->string('pond_identifier');
            $table->decimal('length', 10, 2);
            $table->decimal('width', 10, 2);
            $table->decimal('depth', 10, 2);
            $table->decimal('water_capacity', 15, 2)->nullable();
            $table->string('hatchery_reference')->nullable();
            // Stores array of {speciesName, quantity, perPieceWeight, totalBiomass}
            $table->json('species_stocked')->nullable();
            $table->date('stocking_date');
            $table->enum('status', ['active', 'harvested'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ponds');
    }
};
