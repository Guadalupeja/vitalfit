<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('product');
            $table->string('presentation')->nullable();
            $table->date('entry_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('segment')->nullable();

            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->default('piezas');
            $table->decimal('minimum_stock', 10, 2)->nullable();

            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['branch_id', 'active']);
            $table->index(['branch_id', 'segment']);
            $table->index(['branch_id', 'expiration_date']);
            $table->index(['branch_id', 'quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};