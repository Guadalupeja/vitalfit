<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialist_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('weekday');
            $table->time('start_time');
            $table->time('end_time');

            $table->string('service_type', 30)->default('nutrition');
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'user_id', 'weekday']);
            $table->index(['service_type', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialist_schedules');
    }
};