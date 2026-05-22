<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_test_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('water_test_id')
                  ->constrained('water_tests')
                  ->onDelete('cascade');

            // Stored path relative to storage/app/public
            $table->string('image_path');

            // Optional label (e.g. "Pond inlet", "Algae bloom area")
            $table->string('caption')->nullable();

            // Controls display order in the gallery (0 = primary)
            $table->tinyInteger('display_order')->unsigned()->default(0);

            $table->timestamps();

            $table->index('water_test_id', 'wti_test_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_test_images');
    }
};
