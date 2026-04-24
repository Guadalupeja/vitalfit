<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'patient_package_id')) {
                $table->unsignedBigInteger('patient_package_id')->nullable()->after('patient_id');

                $table->foreign('patient_package_id')
                    ->references('id')
                    ->on('patient_packages')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'patient_package_id')) {
                $table->dropForeign(['patient_package_id']);
                $table->dropColumn('patient_package_id');
            }
        });
    }
};