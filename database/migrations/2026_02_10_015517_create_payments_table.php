<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            // En Fase 1, todavía no amarramos a citas; lo dejamos listo para Ticket 6
          //  $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();

            $table->dateTime('paid_at');
            $table->decimal('amount', 10, 2);

            $table->string('method', 20); // cash|transfer|card
            $table->string('reference')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['patient_id', 'paid_at']);
            $table->index(['method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
