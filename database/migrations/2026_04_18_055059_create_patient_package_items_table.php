<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_package_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_package_id')
                ->constrained('patient_packages')
                ->cascadeOnDelete();

            $table->foreignId('treatment_id')
                ->constrained('treatments')
                ->restrictOnDelete();

            $table->unsignedInteger('sessions_included')->default(1);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['patient_package_id', 'sort_order']);
            $table->unique(['patient_package_id', 'treatment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_package_items');
    }
};