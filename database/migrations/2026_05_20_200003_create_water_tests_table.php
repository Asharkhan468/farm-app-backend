<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_tests', function (Blueprint $table) {
            $table->id();

            // ── Ownership ─────────────────────────────────────────────────────
            $table->foreignId('farm_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Nullable so a test can be recorded at farm level (no specific pond)
            $table->foreignId('pond_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // Links the test to a specific grow-out cycle
            $table->string('culture_id')->nullable()->index();

            // Who performed the test
            $table->string('agent_name')->nullable();

            // ── Core water chemistry ──────────────────────────────────────────
            // pH: 0.00 – 14.00  (optimal aquaculture: 6.5 – 9.0)
            $table->decimal('ph', 5, 2);

            // Dissolved oxygen in mg/L  (optimal: > 5 mg/L)
            $table->decimal('oxygen', 8, 3);

            // Water temperature in °C
            $table->decimal('temperature', 5, 2);

            // Un-ionized ammonia NH3 in mg/L  (toxic above 0.025 mg/L for most fish)
            $table->decimal('ammonia', 8, 4)->nullable();

            // Nitrite NO2 in mg/L  (toxic above 0.1 mg/L)
            $table->decimal('nitrite', 8, 4)->nullable();

            // Nitrate NO3 in mg/L  (less acute, concern above 50 mg/L)
            $table->decimal('nitrate', 8, 4)->nullable();

            // ── Extended chemistry ────────────────────────────────────────────
            // Total alkalinity in mg/L as CaCO3  (optimal: 75 – 200 mg/L)
            $table->decimal('alkalinity', 8, 2)->nullable();

            // Total hardness in mg/L as CaCO3  (optimal: 50 – 200 mg/L)
            $table->decimal('hardness', 8, 2)->nullable();

            // Turbidity in NTU  (water clarity indicator; optimal: < 30 NTU)
            $table->decimal('turbidity', 8, 3)->nullable();

            // Salinity in ppt (parts per thousand)  (relevant for brackish/marine ponds)
            $table->decimal('salinity', 8, 3)->nullable();

            // Total Dissolved Solids in mg/L
            $table->decimal('tds', 10, 2)->nullable();

            // Dissolved CO2 in mg/L  (high CO2 depresses dissolved oxygen uptake)
            $table->decimal('co2', 8, 3)->nullable();

            // Hydrogen sulfide H2S in mg/L  (lethal above 0.002 mg/L; very small values)
            $table->decimal('hydrogen_sulfide', 10, 5)->nullable();

            // Orthophosphate PO4 in mg/L  (eutrophication indicator; < 0.1 mg/L ideal)
            $table->decimal('phosphate', 8, 4)->nullable();

            // ── Flexible / custom ─────────────────────────────────────────────
            // Stores array of { testName, testValue, unit } for lab-specific parameters
            $table->json('custom_tests')->nullable();

            // ── Trend & diagnosis ─────────────────────────────────────────────
            // Observed reasons for abnormal parameter values
            $table->json('parameter_reasons')->nullable();

            // Which direction the problematic parameter has moved
            $table->enum('parameter_direction', ['up', 'down'])->nullable();

            // Computed severity based on threshold comparison (set in controller)
            $table->enum('overall_status', ['normal', 'warning', 'critical'])
                  ->default('normal');

            // ── Meta ──────────────────────────────────────────────────────────
            $table->text('note')->nullable();
            $table->time('time_recorded')->nullable();
            $table->date('date');

            // Primary image (kept for backward compatibility; full gallery uses water_test_images)
            $table->string('image_path')->nullable();

            $table->timestamps();

            // ── Indexes for common query patterns ─────────────────────────────
            // Most frequent: "all tests for a farm, ordered by date DESC"
            $table->index(['farm_id', 'date'], 'wt_farm_date');

            // "all tests for a specific pond, date range"
            $table->index(['pond_id', 'date'], 'wt_pond_date');

            // Alert dashboard: filter by severity
            $table->index('overall_status', 'wt_status');

            // Standalone date filter (reports, exports)
            $table->index('date', 'wt_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_tests');
    }
};
