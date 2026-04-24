<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_template_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('package_template_id')
                ->constrained('package_templates')
                ->cascadeOnDelete();

            $table->foreignId('treatment_id')
                ->constrained('treatments')
                ->restrictOnDelete();

            $table->unsignedInteger('sessions_included')->default(1);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['package_template_id', 'sort_order']);
            $table->unique(['package_template_id', 'treatment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_template_items');
    }
};