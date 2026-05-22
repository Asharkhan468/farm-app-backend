<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_quality_thresholds', function (Blueprint $table) {
            $table->id();

            // ── Scope ─────────────────────────────────────────────────────────
            $table->foreignId('farm_id')
                  ->constrained()
                  ->onDelete('cascade');

            // NULL = applies to all ponds on the farm; set = pond-specific override
            $table->foreignId('pond_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Species-specific ranges (e.g. "Tilapia" vs "Rohu" differ in pH tolerance)
            $table->string('species_name')->nullable();

            // TRUE when this row is the farm-wide default threshold set
            $table->boolean('is_default')->default(false);

            // ── pH thresholds ─────────────────────────────────────────────────
            $table->decimal('ph_min', 5, 2)->nullable()->comment('Optimal: 6.5');
            $table->decimal('ph_max', 5, 2)->nullable()->comment('Optimal: 9.0');

            // ── Dissolved oxygen ──────────────────────────────────────────────
            $table->decimal('oxygen_min', 8, 3)->nullable()->comment('Optimal: 5.0 mg/L');

            // ── Temperature ───────────────────────────────────────────────────
            $table->decimal('temperature_min', 5, 2)->nullable();
            $table->decimal('temperature_max', 5, 2)->nullable();

            // ── Nitrogen cycle toxins ─────────────────────────────────────────
            $table->decimal('ammonia_max', 8, 4)->nullable()->comment('Safe: < 0.025 mg/L (un-ionized NH3)');
            $table->decimal('nitrite_max', 8, 4)->nullable()->comment('Safe: < 0.1 mg/L');
            $table->decimal('nitrate_max', 8, 4)->nullable()->comment('Safe: < 50 mg/L');

            // ── Physical parameters ───────────────────────────────────────────
            $table->decimal('turbidity_max', 8, 3)->nullable()->comment('Ideal: < 30 NTU');
            $table->decimal('alkalinity_min', 8, 2)->nullable()->comment('Optimal: 75 mg/L');
            $table->decimal('alkalinity_max', 8, 2)->nullable()->comment('Optimal: 200 mg/L');
            $table->decimal('hardness_min', 8, 2)->nullable();
            $table->decimal('hardness_max', 8, 2)->nullable();

            // ── Toxic gases ───────────────────────────────────────────────────
            $table->decimal('hydrogen_sulfide_max', 10, 5)->nullable()->comment('Safe: < 0.002 mg/L');

            // ── Alert configuration ───────────────────────────────────────────
            // Whether breaching this threshold should trigger an alert in the app
            $table->boolean('alert_on_breach')->default(true);

            $table->timestamps();

            // Prevent duplicate threshold sets for the same farm + pond + species combo
            $table->unique(['farm_id', 'pond_id', 'species_name'], 'wqt_farm_pond_species');

            $table->index(['farm_id', 'pond_id'], 'wqt_farm_pond');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_quality_thresholds');
    }
};
