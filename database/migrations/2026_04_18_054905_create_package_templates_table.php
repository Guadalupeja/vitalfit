<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->string('name', 150);
            $table->text('description')->nullable();

            $table->decimal('total_price', 10, 2)->default(0);
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['branch_id', 'active']);
            $table->unique(['branch_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_templates');
    }
};