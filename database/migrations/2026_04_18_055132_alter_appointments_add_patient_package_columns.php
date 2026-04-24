<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('patient_package_id')
                ->nullable()
                ->after('patient_id')
                ->constrained('patient_packages')
                ->nullOnDelete();

            $table->foreignId('patient_package_item_id')
                ->nullable()
                ->after('patient_package_id')
                ->constrained('patient_package_items')
                ->nullOnDelete();

            $table->index(['patient_package_id']);
            $table->index(['patient_package_item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_package_item_id');
            $table->dropConstrainedForeignId('patient_package_id');
        });
    }
};