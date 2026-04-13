<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');
            $table->string('phone', 30)->nullable();
            $table->text('notes')->nullable();

            // Tratamiento elegido actual (Fase 1)
            $table->foreignId('treatment_id')->nullable()->constrained('treatments')->nullOnDelete();

            // Sesiones del paquete (Fase 1)
            $table->unsignedSmallInteger('sessions_purchased')->default(0);

            // Totales del paquete (para adeudo)
            $table->decimal('package_total', 10, 2)->default(0);

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['full_name', 'active']);
            $table->index(['treatment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
