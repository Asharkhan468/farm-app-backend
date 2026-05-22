<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->foreignId('pond_id')->constrained()->onDelete('cascade');
            $table->string('agent_name')->nullable();
            $table->enum('feeding_type', ['commercial', 'natural']);
            $table->string('feed_reference')->nullable();
            $table->string('protein_percentage')->nullable();
            $table->decimal('feeding_weight', 10, 3);
            $table->text('note')->nullable();
            $table->time('feeding_time');
            $table->enum('frequency', ['once', 'twice', 'thrice'])->default('once');
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_records');
    }
};
