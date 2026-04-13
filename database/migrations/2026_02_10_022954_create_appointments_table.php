<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained('treatments')->nullOnDelete();

            // Especialista asignado a la cita
            $table->foreignId('specialist_id')->constrained('users')->cascadeOnDelete();

            // Quién la agendó (puede ser recepción u otro especialista)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->string('status', 20)->default('confirmed'); // confirmed|cancelled|completed|no_show|pending
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['specialist_id', 'start_at']);
            $table->index(['patient_id']);
            $table->index(['treatment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
