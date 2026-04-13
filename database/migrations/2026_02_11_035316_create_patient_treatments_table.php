<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('treatment_id')->constrained('treatments')->restrictOnDelete();

            $table->unsignedSmallInteger('sessions_purchased')->default(0);
            $table->decimal('package_total', 10, 2)->default(0);

            $table->string('status', 20)->default('active'); // active|paused|finished|cancelled
            $table->date('started_on')->nullable();
            $table->date('ends_on')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['patient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_treatments');
    }
};
